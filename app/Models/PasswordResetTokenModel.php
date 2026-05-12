<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetTokenModel extends Model
{
    protected $table            = 'password_reset_tokens';
    protected $primaryKey       = 'email';
    protected $returnType       = 'array';
    protected $useAutoIncrement = false;
    protected $useTimestamps    = false;
    protected $allowedFields    = ['email', 'token', 'created_at'];
}
