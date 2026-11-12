<?php
return [
 'roles'=>['admin'=>'مدير النظام الرئيسي','executive'=>'الإدارة','sales_manager'=>'مدير مبيعات','sales'=>'موظف مبيعات','marketing'=>'التسويق','operations'=>'التشغيل','finance'=>'المالية','system_admin'=>'مسؤول الحسابات والنظام'],
 'abilities'=>[
 'executive'=>['accounts.read','sales.read','sales.all','orders.read','finance.read','campaigns.read','reports','audit'],
 'sales_manager'=>['accounts.read','accounts.write','sales.read','sales.write','sales.team','sales.assign','quotes.approve','orders.read','tasks.assign','reports'],
 'sales'=>['accounts.read','accounts.write','sales.read','sales.write','orders.read'],
 'marketing'=>['accounts.read','accounts.write','leads.capture','campaigns.read','campaigns.write','content','reports'],
 'operations'=>['orders.read','orders.write','cases.write'],
 'finance'=>['orders.read','finance.read','finance.write'],
 'system_admin'=>['users','audit'],
 ],
 'stages'=>['new'=>'جديد','qualified'=>'مؤهل','needs'=>'تحديد الاحتياج','sample'=>'عينة','quoted'=>'عرض سعر','negotiation'=>'تفاوض','won'=>'تم الفوز','lost'=>'خسارة'],
 'sources'=>['website'=>'الموقع','manual'=>'إدخال يدوي','exhibition'=>'معرض','ads'=>'إعلان','referral'=>'توصية','outbound'=>'تواصل مباشر','import'=>'استيراد ملف'],
 'segments'=>['distributor'=>'موزع','importer'=>'مستورد','restaurant'=>'مطاعم','retail'=>'تجزئة','private_label'=>'علامة خاصة'],
 'order_states'=>['pending'=>'مراجعة الطلب','preparing'=>'قيد التجهيز','ready'=>'جاهز للشحن','shipped'=>'تم الشحن','delivered'=>'تم التسليم','cancelled'=>'ملغي'],
 'currencies'=>['EGP','USD','EUR','SAR','AED'],
];
