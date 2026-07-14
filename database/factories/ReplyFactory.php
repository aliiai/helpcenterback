<?php

namespace Database\Factories;

use App\Models\Reply;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reply>
 */
class ReplyFactory extends Factory
{
    /**
     * @var list<string>
     */
    public static array $customerBodies = [
        'شكراً لكم، بانتظار الرد والمتابعة.',
        'تم تجربة الخطوات ولم تُحل المشكلة حتى الآن.',
        'أرفقت تفاصيل إضافية لتتضح الصورة بشكل أفضل.',
        'هل تحتاجون أي معلومة أخرى من جهتي؟',
        'حدثت المشكلة مرة أخرى اليوم صباحاً.',
        'ممتاز، سأجرب الحل وأعود إليكم بالنتيجة.',
        'أرجو تسريع المعالجة إن أمكن لأنها تؤثر على عملي.',
    ];

    /**
     * @var list<string>
     */
    public static array $adminBodies = [
        'شكراً لتواصلك معنا. نعمل حالياً على مراجعة المشكلة.',
        'تم تحويل التذكرة للفريق المختص وسنوافيك بالتحديث قريباً.',
        'يرجى تجربة تسجيل الخروج ثم الدخول مجدداً وإخبارنا بالنتيجة.',
        'تلقينا التفاصيل وبدأنا التحقق من السجلات المتعلقة بحسابك.',
        'تم حل المشكلة من جهتنا، نرجو التأكد من جانبك وتأكيد الإغلاق.',
        'نحتاج منك تزويدنا برقم العملية وتاريخ حدوث المشكلة بالضبط.',
        'قمنا بتحديث الحالة إلى قيد المعالجة ونتابع الأمر معك.',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'body' => fake()->randomElement([
                ...static::$customerBodies,
                ...static::$adminBodies,
            ]),
        ];
    }

    public function fromCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'body' => fake()->randomElement(static::$customerBodies),
        ]);
    }

    public function fromAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'body' => fake()->randomElement(static::$adminBodies),
        ]);
    }
}
