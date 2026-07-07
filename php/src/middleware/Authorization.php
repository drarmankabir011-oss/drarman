<?php

namespace Middleware;

use Config\Constants;

class Authorization {
    private $permissions = [
        Constants::ROLE_ADMIN => ['*'],
        Constants::ROLE_CONSULTANT => [
            'patients.view',
            'patients.create',
            'patients.edit',
            'visits.create',
            'prescriptions.create',
            'vitals.review',
            'soap_notes.finalize',
            'appointments.view',
            'appointments.create',
            'bed.manage',
        ],
        Constants::ROLE_REGISTRAR => [
            'patients.view',
            'patients.create',
            'visits.view',
            'vitals.review',
            'soap_notes.view',
            'prescriptions.view',
            'appointments.view',
        ],
        Constants::ROLE_MEDICAL_OFFICER => [
            'patients.view',
            'visits.view',
            'vitals.verify',
            'soap_notes.review',
            'prescriptions.view',
            'investigations.view',
        ],
        Constants::ROLE_ASSISTANT_REGISTRAR => [
            'patients.view',
            'visits.view',
            'vitals.record',
            'soap_notes.view',
        ],
        Constants::ROLE_INTERN => [
            'patients.view',
            'soap_notes.create',
            'vitals.record',
            'visits.view',
        ],
        Constants::ROLE_NURSE => [
            'vitals.record',
            'patients.view',
            'appointments.view',
            'prescriptions.view',
        ],
        Constants::ROLE_RECEPTION => [
            'patients.create',
            'patients.view',
            'appointments.book',
            'appointments.view',
            'payments.record',
            'invoices.view',
        ],
        Constants::ROLE_PATIENT => [
            'profile.view_own',
            'appointments.book',
            'prescriptions.download',
            'visits.view_own',
        ],
    ];

    public function hasPermission($user_role, $action) {
        if (!isset($this->permissions[$user_role])) {
            return false;
        }

        $userPermissions = $this->permissions[$user_role];
        
        if (in_array('*', $userPermissions)) {
            return true;
        }

        return in_array($action, $userPermissions);
    }

    public function authorize($user_role, $action) {
        if (!$this->hasPermission($user_role, $action)) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized', 'action' => $action]);
            exit;
        }
    }

    public function getPermissionsForRole($role) {
        return $this->permissions[$role] ?? [];
    }
}
