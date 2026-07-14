<?php

namespace App\Http\Controllers\Api;

use App\Actions\NotifyAdminsAction;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreUserRequest;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Notifications\UserRegisteredNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(private NotifyAdminsAction $notifyAdmins) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->when(
                $request->filled('search'),
                function ($query) use ($request): void {
                    $search = '%'.$request->string('search')->toString().'%';
                    $query->where(function ($query) use ($search): void {
                        $query->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
                },
            )
            ->when(
                $request->filled('role'),
                function ($query) use ($request): void {
                    $role = UserRole::tryFrom($request->string('role')->toString());

                    if ($role === null) {
                        throw ValidationException::withMessages([
                            'role' => ['The selected role is invalid.'],
                        ]);
                    }

                    $query->where('role', $role);
                },
            )
            ->latest()
            ->paginate(15);

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->safe()->only(['name', 'email', 'password', 'role']));

        $this->notifyAdmins->handle(
            new UserRegisteredNotification($user),
            $request->user()->id,
        );

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        $this->authorize('view', $user);

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $data = $request->safe()->only(['name', 'email', 'role']);

        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        $user->update($data);

        return new UserResource($user->fresh());
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        if ($user->isAdmin() && User::query()->where('role', UserRole::Admin)->count() <= 1) {
            throw ValidationException::withMessages([
                'user' => ['Cannot delete the last admin account.'],
            ]);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
