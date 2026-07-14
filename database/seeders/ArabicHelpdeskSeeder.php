<?php

namespace Database\Seeders;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Attachment;
use App\Models\Reply;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArabicHelpdeskSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'مدير النظام',
                'password' => 'password',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ],
        );

        $this->seedAliDemoAccount($admin);
        $this->seedTenUsersWithTickets($admin);
    }

    private function seedAliDemoAccount(User $admin): void
    {
        $ali = User::query()->updateOrCreate(
            ['email' => 'ali@gmail.com'],
            [
                'name' => 'علي أحمد',
                'password' => 'password',
                'role' => UserRole::User,
                'email_verified_at' => now(),
            ],
        );

        $this->purgeUserTickets($ali);

        $demoTickets = [
            [
                'title' => 'تعذّر تسجيل الدخول بعد تحديث التطبيق',
                'description' => 'بعد تحديث التطبيق إلى آخر إصدار أصبحت رسالة "بيانات الدخول غير صحيحة" تظهر باستمرار رغم أن كلمة المرور صحيحة. أرجو المساعدة لاستعادة الوصول.',
                'status' => TicketStatus::Open,
                'replies' => [
                    ['by' => 'customer', 'body' => 'بدأت المشكلة منذ صباح اليوم بعد التحديث مباشرة.'],
                    ['by' => 'admin', 'body' => 'شكراً يا علي على التوضيح. نتحقق حالياً من خدمة المصادقة المرتبطة بحسابك.'],
                    ['by' => 'customer', 'body' => 'جربت أيضاً إعادة تعيين كلمة المرور ولم يصلني البريد.'],
                    ['by' => 'admin', 'body' => 'تم رصد تأخر في طابور إرسال البريد. سنصلح ذلك ونعود إليك خلال ساعة.'],
                ],
            ],
            [
                'title' => 'استرداد مبلغ مخصوم بالخطأ',
                'description' => 'تم خصم اشتراك الشهر مرتين من بطاقتي البنكية. قيمة كل عملية 149 ريالاً، والتاريخ اليوم نفسه بفارق دقائق.',
                'status' => TicketStatus::InProgress,
                'replies' => [
                    ['by' => 'admin', 'body' => 'مرحباً علي، استلمنا البلاغ وبدأنا مطابقة القيود المالية مع بوابة الدفع.'],
                    ['by' => 'customer', 'body' => 'أرفقت صورة من كشف الحساب البنكي يظهر فيه الخصمان.'],
                    ['by' => 'admin', 'body' => 'تأكدنا من وجود تكرار في العملية. سيتم إرجاع المبلغ خلال 3 إلى 5 أيام عمل.'],
                    ['by' => 'customer', 'body' => 'شكراً جزيلاً، بانتظار وصول المبلغ المسترد.'],
                    ['by' => 'admin', 'body' => 'تم اعتماد طلب الاسترداد برقم REF- thr4281، ويمكنك متابعته من البنك لاحقاً.'],
                ],
            ],
            [
                'title' => 'المرفقات لا تُرفع داخل التذكرة',
                'description' => 'عند محاولة إرفاق صورة بصيغة PNG بحجم حوالي 1 ميجابايت تفشل العملية مع رسالة عامة بدون تفاصيل.',
                'status' => TicketStatus::InProgress,
                'replies' => [
                    ['by' => 'customer', 'body' => 'حدث ذلك من متصفح كروم ومن الجوال أيضاً.'],
                    ['by' => 'admin', 'body' => 'نشكرك على الإبلاغ. يبدو أن هناك قيداً مؤقتاً على مسار التخزين، ونعمل على إصلاحه الآن.'],
                    ['by' => 'admin', 'body' => 'تم رفع الحد الأقصى وإعادة ضبط صلاحيات المجلد. جرّب الرفع مجدداً من فضلك.'],
                    ['by' => 'customer', 'body' => 'نجحت المحاولة الآن بعد التحديث. شكراً للدعم السريع.'],
                ],
            ],
            [
                'title' => 'طلب تغيير البريد المرتبط بالحساب',
                'description' => 'أرغب بنقل الحساب من بريد قديم إلى ali@gmail.com بشكل نهائي مع الاحتفاظ بكل التذاكر السابقة.',
                'status' => TicketStatus::Closed,
                'replies' => [
                    ['by' => 'admin', 'body' => 'يمكن تنفيذ الطلب بعد التحقق من ملكية الحساب. هل يمكنك تأكيد رقم الجوال المسجل؟'],
                    ['by' => 'customer', 'body' => 'نعم، رقم الجوال هو نفسه المسجل في الملف الشخصي وينتهي بـ 44.'],
                    ['by' => 'admin', 'body' => 'تم التحقق وتحديث البريد بنجاح. يمكنك الدخول بالبريد الجديد من الآن.'],
                    ['by' => 'customer', 'body' => 'ممتاز، جربت الدخول ويعمل بدون مشاكل.'],
                    ['by' => 'admin', 'body' => 'سعيدون أن الأمر اكتمل. سنغلق التذكرة، ويمكنك فتح جديدة عند الحاجة.'],
                ],
            ],
            [
                'title' => 'الإشعارات لا تصل عند رد الدعم',
                'description' => 'لاحظت أني لا أستلم أي بريد عند إضافة رد جديد من فريق الدعم، رغم تفعيل خيار الإشعارات في الإعدادات.',
                'status' => TicketStatus::Open,
                'replies' => [
                    ['by' => 'customer', 'body' => 'راجعت صندوق الوارد والمهملات ولم أجد شيئاً.'],
                    ['by' => 'admin', 'body' => 'شكراً للتنبيه. سنراجع إعدادات الاشتراك بالإشعارات على حسابك.'],
                    ['by' => 'admin', 'body' => 'وجدنا أن التفعيل لم يُحفظ بشكل صحيح. أعدنا ضبطه وأرسلنا رسالة اختبار الآن.'],
                ],
            ],
            [
                'title' => 'استفسار عن ترقية الباقة الشهرية',
                'description' => 'أحتاج مقارنة سريعة بين الباقة الحالية والباقة الاحترافية، خصوصاً حدود المستخدمين ومساحة المرفقات.',
                'status' => TicketStatus::Closed,
                'replies' => [
                    ['by' => 'admin', 'body' => 'مرحباً علي، الباقة الاحترافية تتيح 20 مستخدماً ورفع مرفقات حتى 25 ميجابايت للملف.'],
                    ['by' => 'customer', 'body' => 'هل يمكن الترقية فوراً مع احتساب المتبقي من الاشتراك الحالي؟'],
                    ['by' => 'admin', 'body' => 'نعم، يتم احتساب الفرق تلقائياً عند الترقية من صفحة الاشتراكات.'],
                    ['by' => 'customer', 'body' => 'تمّت الترقية بنجاح. شكراً على التوضيح.'],
                    ['by' => 'admin', 'body' => 'على الرحب والسعة. نتمنى لك تجربة أفضل مع الباقة الجديدة.'],
                ],
            ],
        ];

        foreach ($demoTickets as $index => $payload) {
            $ticket = Ticket::query()->create([
                'user_id' => $ali->id,
                'title' => $payload['title'],
                'description' => $payload['description'],
                'status' => $payload['status'],
                'created_at' => now()->subDays(count($demoTickets) - $index),
                'updated_at' => now()->subDays(count($demoTickets) - $index),
            ]);

            foreach ($payload['replies'] as $replyIndex => $reply) {
                Reply::query()->create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $reply['by'] === 'admin' ? $admin->id : $ali->id,
                    'body' => $reply['body'],
                    'created_at' => $ticket->created_at->copy()->addHours($replyIndex + 1),
                    'updated_at' => $ticket->created_at->copy()->addHours($replyIndex + 1),
                ]);
            }
        }
    }

    private function seedTenUsersWithTickets(User $admin): void
    {
        $arabicNames = [
            'سارة محمد',
            'خالد العتيبي',
            'نورة الشمري',
            'يوسف الحربي',
            'ريم القحطاني',
            'محمد الدوسري',
            'لينا الغامدي',
            'فهد المطيري',
            'هدى الزهراني',
            'عمر السبيعي',
        ];

        foreach ($arabicNames as $index => $name) {
            $user = User::query()->updateOrCreate(
                ['email' => 'user'.($index + 1).'@example.com'],
                [
                    'name' => $name,
                    'password' => 'password',
                    'role' => UserRole::User,
                    'email_verified_at' => now(),
                ],
            );

            $this->purgeUserTickets($user);

            $ticketCount = random_int(3, 5);

            for ($t = 0; $t < $ticketCount; $t++) {
                $ticket = Ticket::factory()->create([
                    'user_id' => $user->id,
                ]);

                $replyCount = random_int(3, 5);

                for ($r = 0; $r < $replyCount; $r++) {
                    $isAdminReply = $r % 2 === 1;

                    Reply::factory()
                        ->when($isAdminReply, fn ($factory) => $factory->fromAdmin())
                        ->when(! $isAdminReply, fn ($factory) => $factory->fromCustomer())
                        ->create([
                            'ticket_id' => $ticket->id,
                            'user_id' => $isAdminReply ? $admin->id : $user->id,
                        ]);
                }
            }
        }
    }

    private function purgeUserTickets(User $user): void
    {
        $ticketIds = $user->tickets()->pluck('id');

        if ($ticketIds->isEmpty()) {
            return;
        }

        $replyIds = Reply::query()->whereIn('ticket_id', $ticketIds)->pluck('id');

        Attachment::query()
            ->where(function ($query) use ($ticketIds, $replyIds): void {
                $query->where(function ($query) use ($ticketIds): void {
                    $query->where('attachable_type', Ticket::class)
                        ->whereIn('attachable_id', $ticketIds);
                })->orWhere(function ($query) use ($replyIds): void {
                    $query->where('attachable_type', Reply::class)
                        ->whereIn('attachable_id', $replyIds);
                });
            })
            ->delete();

        $user->tickets()->delete();
    }
}
