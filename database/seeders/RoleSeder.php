<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'display_name' => 'Admin', // optional
            'description' => 'Highest Level', // optional
        ]);

        $user = Role::firstOrCreate([
            'name' => 'user',
            'display_name' => 'user', // optional
            'description' => 'Second level', // optional
        ]);

        $adminPermissionArray = [];
        $userPermissionArray = [];

        $api = [
            'api-users' => 'API Users',
            'api-permissions' => 'API Permissions',
        ];
        foreach ($api as $key => $value) {
            /**Create Permission to INDEX */
            $index = Permission::firstOrCreate(
                ['name' => $key.'-index', 'display_name' => $value.' Index']
            );

            /**Create Permission to CREATE */
            $store = Permission::firstOrCreate(
                ['name' => $key.'-store', 'display_name' => $value.' Create']
            );

            /**Create Permission to SHOW */
            $show = Permission::firstOrCreate(
                ['name' => $key.'-show', 'display_name' => $value.' Show']
            );

            /**Create Permission to UPDATE */
            $update = Permission::firstOrCreate(
                ['name' => $key.'-update', 'display_name' => $value.' Update']
            );

            /**Create Permission to DESTROY */
            $destroy = Permission::firstOrCreate(
                ['name' => $key.'-destroy', 'display_name' => $value.' Destroy']
            );

            /**Attach Permission to Admin */
            $adminPermissionArray[] = $index->id;
            $adminPermissionArray[] = $store->id;
            $adminPermissionArray[] = $show->id;
            $adminPermissionArray[] = $update->id;
            $adminPermissionArray[] = $destroy->id;
        }

        $api = [
            'api-articles' => 'API Articles',
            'api-comments' => 'API Comments',
        ];
        foreach ($api as $key => $value) {
            /**Create Permission to INDEX */
            $index = Permission::firstOrCreate(
                ['name' => $key.'-index', 'display_name' => $value.' Index']
            );

            /**Create Permission to CREATE */
            $store = Permission::firstOrCreate(
                ['name' => $key.'-store', 'display_name' => $value.' Create']
            );

            /**Create Permission to SHOW */
            $show = Permission::firstOrCreate(
                ['name' => $key.'-show', 'display_name' => $value.' Show']
            );

            /**Create Permission to UPDATE */
            $update = Permission::firstOrCreate(
                ['name' => $key.'-update', 'display_name' => $value.' Update']
            );

            /**Create Permission to DESTROY */
            $destroy = Permission::firstOrCreate(
                ['name' => $key.'-destroy', 'display_name' => $value.' Destroy']
            );

            /**Attach Permission to Admin */
            $adminPermissionArray[] = $index->id;
            $adminPermissionArray[] = $store->id;
            $adminPermissionArray[] = $show->id;
            $adminPermissionArray[] = $update->id;
            $adminPermissionArray[] = $destroy->id;

            /**Attach Permission to User */
            $userPermissionArray[] = $index->id;
            $userPermissionArray[] = $store->id;
            $userPermissionArray[] = $show->id;
            $userPermissionArray[] = $update->id;
            $userPermissionArray[] = $destroy->id;
        }

        /**User only */
        $requestApprovalPerms = Permission::firstOrCreate(
            ['name' => 'api-articles-request-approval', 'display_name' => 'API Articles Request Approval']
        );
        $userPermissionArray[] = $requestApprovalPerms->id;

        /**Admin only */
        $api = [
            'api-articles-approve' => 'API Articles Approve',
            'api-articles-reject' => 'API Articles Reject',
        ];
        foreach ($api as $key => $value) {
            $perms = Permission::firstOrCreate(
                ['name' => $key, 'display_name' => $value]
            );
            $adminPermissionArray[] = $perms->id;
        }

        /**Admin and User */
        $api = [
            'api-articles-publish' => 'API Articles Publish',
            'api-articles-unpublish' => 'API Articles Unpublish',
            'api-articles-toggle-reaction' => 'API Articles Toggle Reaction',
            'api-comments-toggle-reaction' => 'API Comments Toggle Reaction',
        ];
        foreach ($api as $key => $value) {
            $perms = Permission::firstOrCreate(
                ['name' => $key, 'display_name' => $value]
            );
            $adminPermissionArray[] = $perms->id;
            $userPermissionArray[] = $perms->id;
        }

        $api = [
            'api-notifications' => 'API Notifications',
        ];
        foreach ($api as $key => $value) {
            /**Create Permission to INDEX */
            $index = Permission::firstOrCreate(
                ['name' => $key.'-index', 'display_name' => $value.' Index']
            );

            /**Create Permission to CREATE */
            $store = Permission::firstOrCreate(
                ['name' => $key.'-store', 'display_name' => $value.' Create']
            );

            /**Create Permission to SHOW */
            $show = Permission::firstOrCreate(
                ['name' => $key.'-show', 'display_name' => $value.' Show']
            );

            /**Create Permission to UPDATE */
            $update = Permission::firstOrCreate(
                ['name' => $key.'-update', 'display_name' => $value.' Update']
            );

            /**Create Permission to DESTROY */
            $destroy = Permission::firstOrCreate(
                ['name' => $key.'-destroy', 'display_name' => $value.' Destroy']
            );

            /**Attach Permission to Admin */
            $adminPermissionArray[] = $index->id;
            $adminPermissionArray[] = $show->id;

            /**Attach Permission to User */
            $userPermissionArray[] = $index->id;
            $userPermissionArray[] = $show->id;
        }

        /**Attach Permission to Admin */
        $admin->permissions()->sync($adminPermissionArray);

        /**Attach Permission to User */
        $user->permissions()->sync($userPermissionArray);

        DB::commit();
    }
}
