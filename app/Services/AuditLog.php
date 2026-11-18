<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AuditLog
{
    public static function record(string $action, string $entity, int $id, mixed $before, mixed $after): void
    {
        // Passwords and session secrets must never enter the audit trail.
        $clean = function ($data) {
            $data = (array) $data;
            unset($data['password'], $data['remember_token']);

            return json_encode($data, JSON_UNESCAPED_UNICODE);
        };
        DB::table('audit_logs')->insert(['user_id' => auth()->id(), 'action' => $action,
            'entity_type' => $entity, 'entity_id' => $id, 'before_data' => $clean($before),
            'after_data' => $clean($after), 'created_at' => now()]);
    }
}
