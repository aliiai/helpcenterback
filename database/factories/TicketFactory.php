<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * @var list<array{title: string, description: string}>
     */
    public static array $scenarios = [
        [
            'title' => 'تعذّر تسجيل الدخول إلى الحساب',
            'description' => 'عند إدخال البريد وكلمة المرور تظهر رسالة خطأ رغم أن البيانات صحيحة. أرجو المساعدة في استعادة الوصول للحساب.',
        ],
        [
            'title' => 'لم تصل رسالة إعادة تعيين كلمة المرور',
            'description' => 'طلبت إعادة تعيين كلمة المرور عدة مرات ولم يصل أي بريد إلى صندوق الوارد أو الرسائل غير المرغوب فيها.',
        ],
        [
            'title' => 'مشكلة في عرض الفاتورة الشهرية',
            'description' => 'صفحة الفواتير لا تفتح وتعرض شاشة بيضاء. احتجت الفاتورة لأرشفة المحاسبة في الشركة.',
        ],
        [
            'title' => 'تم الخصم مرتين من البطاقة',
            'description' => 'ظهر خصمان بنفس المبلغ في كشف الحساب البنكي لهذا الشهر. أرجو التحقق وإرجاع المبلغ الزائد إن وجد.',
        ],
        [
            'title' => 'بطء شديد أثناء رفع الملفات',
            'description' => 'عند رفع مرفقات أكبر من 2 ميجابايت تتوقف العملية أو تفشل. الاتصال بالإنترنت مستقر من جهازي.',
        ],
        [
            'title' => 'طلب تفعيل خاصية الإشعارات البريدية',
            'description' => 'أرغب بتفعيل التنبيهات على بريدي عند تحديث حالة التذاكر حتى أتابع الردود بسرعة.',
        ],
        [
            'title' => 'خطأ في تحديث بيانات الملف الشخصي',
            'description' => 'بعد تعديل رقم الجوال والضغط على حفظ، تبقى البيانات القديمة دون تغيير. جربت متصفحين مختلفين.',
        ],
        [
            'title' => 'الحساب مقفل بدون سبب واضح',
            'description' => 'ظهر تنبيه بأن الحساب موقوف مؤقتاً ولم أتلقَّ رسالة توضيحية. أحتاج مراجعة سريعة لإعادة التفعيل.',
        ],
        [
            'title' => 'استفسار عن ترقية الباقة',
            'description' => 'أرغب بالترقية إلى الباقة الأعلى ومعرفة الفرق في المزايا وطريقة الدفع المتاحة حالياً.',
        ],
        [
            'title' => 'واجهة النظام لا تدعم العربية بشكل كامل',
            'description' => 'بعض النصوص في لوحة التحكم ما زالت بالإنجليزية، وخصوصاً في صفحة الإعدادات والتقارير.',
        ],
        [
            'title' => 'مشكلة مزامنة البيانات بين الأجهزة',
            'description' => 'التعديلات التي أجريها من الجوال لا تظهر على الحاسوب إلا بعد تسجيل الخروج والدخول مجدداً.',
        ],
        [
            'title' => 'تعذّر طباعة تقرير الدعم الفني',
            'description' => 'زر الطباعة لا يعمل في صفحة التقارير ويظهر خطأ في وحدة التحكم بالمتصفح.',
        ],
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scenario = fake()->randomElement(static::$scenarios);

        return [
            'user_id' => User::factory(),
            'title' => $scenario['title'],
            'description' => $scenario['description'],
            'status' => fake()->randomElement([
                TicketStatus::Open,
                TicketStatus::InProgress,
                TicketStatus::Closed,
            ]),
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::Open,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::InProgress,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::Closed,
        ]);
    }
}
