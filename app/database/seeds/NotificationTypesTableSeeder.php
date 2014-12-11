<?php

use Paxifi\Notification\Repository\EloquentNotificationTypeRepository;

class NotificationTypesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('notification_types')->truncate();

        EloquentNotificationTypeRepository::create(
            [
                "name" => "Email Notification",
                "type" => "email"
            ]
        );

        EloquentNotificationTypeRepository::create(
            [
                "name" => "Product Inventory Notification",
                "type" => "inventory"
            ]
        );

        EloquentNotificationTypeRepository::create(
            [
                "name" => "Thumbs Notification",
                "type" => "thumbs"
            ]
        );

        EloquentNotificationTypeRepository::create(
            [
                "name" => "Sales Notification",
                "type" => "sales"
            ]
        );

        EloquentNotificationTypeRepository::create(
            [
                "name" => "Billing Notification",
                "type" => "billing"
            ]
        );

        EloquentNotificationTypeRepository::create(
            [
                "name" => "Subscription Notification",
                "type" => "subscription"
            ]
        );
    }
} 