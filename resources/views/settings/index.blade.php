@extends('layouts.app')

@section('title', 'الإعدادات')
@section('page_title', 'الإعدادات')
@section('page_subtitle', 'إعدادات النظام والشركة')

@section('content')
<div class="space-y-6" x-data="{ tab: 'company' }">

    <div class="flex items-center gap-2 border-b border-slate-200">
        <button @click="tab = 'company'" :class="tab === 'company' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500'"
                class="px-4 py-2.5 text-sm font-medium border-b-2 transition">بيانات الشركة</button>
        <button @click="tab = 'invoices'" :class="tab === 'invoices' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500'"
                class="px-4 py-2.5 text-sm font-medium border-b-2 transition">إعدادات الفواتير</button>
        <button @click="tab = 'categories'" :class="tab === 'categories' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500'"
                class="px-4 py-2.5 text-sm font-medium border-b-2 transition">التصنيفات</button>
    </div>

    <form method="POST" action="{{ route('settings.update') }}" class="space-y-6" x-show="tab === 'company' || tab === 'invoices'" style="display:none">
        @csrf @method('PUT')

        <x-card title="بيانات الشركة" x-show="tab === 'company'" style="display:none">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="اسم الشركة" name="company_name" :value="\App\Models\Setting::get('company_name')" required />
                <x-input label="البريد الإلكتروني" name="company_email" type="email" :value="\App\Models\Setting::get('company_email')" />
                <x-input label="رقم الهاتف" name="company_phone" :value="\App\Models\Setting::get('company_phone')" />
                <x-input label="العملة" name="currency" :value="\App\Models\Setting::get('currency', 'SAR')" required />
                <x-input label="رمز العملة" name="currency_symbol" :value="\App\Models\Setting::get('currency_symbol', 'ر.س')" required />
                <div class="md:col-span-2">
                    <x-textarea label="العنوان" name="company_address" :value="\App\Models\Setting::get('company_address')" />
                </div>
            </div>
        </x-card>

        <x-card title="إعدادات الفواتير" x-show="tab === 'invoices'" style="display:none">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="أيام الاستحقاق الافتراضية" name="invoice_due_days" type="number" :value="\App\Models\Setting::get('invoice_due_days', 30)" />
                <x-input label="بادئة فواتير المبيعات" name="invoice_prefix_sales" :value="\App\Models\Setting::get('invoice_prefix_sales', 'INV-S-')" />
                <x-input label="بادئة فواتير المشتريات" name="invoice_prefix_purchase" :value="\App\Models\Setting::get('invoice_prefix_purchase', 'INV-P-')" />
                <div class="md:col-span-2">
                    <x-textarea label="ملاحظات الفواتير" name="invoice_notes" :value="\App\Models\Setting::get('invoice_notes')" />
                </div>
            </div>
        </x-card>

        <div>
            <x-button type="submit" icon="check">حفظ الإعدادات</x-button>
        </div>
    </form>

    <div x-show="tab === 'categories'" style="display:none" class="space-y-4">
        <x-card title="إضافة تصنيف جديد">
            <form method="POST" action="{{ route('settings.categories.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                @csrf
                <x-input label="اسم التصنيف" name="name" required class="md:col-span-2" />
                <x-select label="النوع" name="type" :options="['purchase' => 'مشتريات', 'invoice' => 'فواتير', 'general' => 'عام']" required :placeholder="null" />
                <div class="flex items-end">
                    <x-button type="submit" icon="plus">إضافة</x-button>
                </div>
            </form>
        </x-card>

        <x-card title="التصنيفات الحالية">
            @if ($categories->isEmpty())
                <x-empty-state title="لا توجد تصنيفات" subtitle="أضف تصنيفًا جديدًا" icon="tree" />
            @else
                <div class="divide-y divide-slate-100 -my-2">
                    @foreach ($categories as $cat)
                        <div class="flex items-center justify-between py-3">
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full" style="background: {{ $cat->color }}"></span>
                                <span class="text-sm font-semibold">{{ $cat->name }}</span>
                                <x-badge :color="['purchase' => 'rose', 'invoice' => 'emerald', 'general' => 'slate'][$cat->type]">{{ ['purchase' => 'مشتريات', 'invoice' => 'فواتير', 'general' => 'عام'][$cat->type] }}</x-badge>
                            </div>
                            <form method="POST" action="{{ route('settings.categories.destroy', $cat) }}" onsubmit="return confirm('حذف التصنيف؟')">
                                @csrf @method('DELETE')
                                <button class="text-slate-400 hover:text-rose-600"><x-icon name="trash" class="w-4 h-4" /></button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>

</div>
@endsection
