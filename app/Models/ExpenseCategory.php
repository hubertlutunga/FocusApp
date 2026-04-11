<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ExpenseCategory extends Model
{
    public function options(): array
    {
        $statement = $this->db->query('SELECT id, name, description FROM expense_categories ORDER BY name ASC');
        return $statement->fetchAll();
    }
}
