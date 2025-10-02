<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'officer';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('superadmin');
     *
     * @var array<string, array<string, string>>
     *
     * @see https://codeigniter4.github.io/shield/quick_start_guide/using_authorization/#change-available-groups for more info
     */
    public array $groups = [
        'superadmin' => [
            'title'       => 'Super Admin',
            'description' => 'เข้าถึงส่วนจัดการแบบเบ็ดเสร็จ',
        ],
        'admin' => [
            'title'       => 'Admin',
            'description' => 'ผู้ดูแลระบบ',
        ],
        'it' => [
            'title'       => 'IT Officer',
            'description' => 'ผู้ดูแลและพัฒนาเว็บไซต์',
        ],
        'officer' => [
            'title'       => 'Officer',
            'description' => 'ผู้ใช้งานทั่วไป',
        ]
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [
        'admin.access'        => 'Can access the sites admin area', // สามารถเข้าถึงหน้า Admin Dashboard ได้
        'admin.settings'      => 'Can access the main site settings', // สามารถเข้าถึงหน้า Dashboard Settings ได้
        'users.manage-admins' => 'Can manage other admins', // สามารถจัดการบัญชี Admin ได้
        'users.create'        => 'Can create new non-admin users', // สามารถสร้างบัญชีผู้ใช้งานทั่วไปได้ (ที่ไม่ใช่ Admin)
        'users.edit'          => 'Can edit existing non-admin users', // สามารถแก้ไขบัญชีผู้ใช้งานทั่วไปได้ (ที่ไม่ใช่ Admin)
        'users.delete'        => 'Can delete existing non-admin users', // สามารถลบบัญชีผู้ใช้งานทั่วไปได้ (ที่ไม่ใช่ Admin)

        'officer.meeting.access'   => 'สามารถเข้าถึงระบบการจองห้องประชุมได้',
        'officer.meeting.approval' => 'สามารถอนุมัติการจองห้องประชุมได้',
        'officer.meeting.booking'  => 'สามารถจองห้องประชุมได้',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [
        'superadmin' => [
            'admin.*',
            'users.*',
            'officer.*',
        ],
        'admin' => [
            'admin.access',
            'users.create',
            'users.edit',
            'users.delete',
            'officer.*',
        ],
        'it' => [
            'admin.access',
            'admin.settings',
            'users.create',
            'users.edit',
            'officer.meeting.access',
            'officer.meeting.booking',
        ],
        'officer' => [
            'officer.meeting.access',
            'officer.meeting.booking',
        ],
    ];
}
