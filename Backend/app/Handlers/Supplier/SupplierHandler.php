<?php

declare(strict_types=1);

final class SupplierHandler extends MasterDataHandler
{
    protected string $table = 'suppliers';
    protected string $title = 'Supplier';
    protected string $viewFolder = 'supplier';
    protected array $extraFields = ['contact_name', 'phone', 'email', 'address', 'notes'];
}