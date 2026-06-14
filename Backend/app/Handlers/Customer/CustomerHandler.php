<?php

declare(strict_types=1);

final class CustomerHandler extends MasterDataHandler
{
    protected string $table = 'customers';
    protected string $title = 'Customer';
    protected string $viewFolder = 'customer';
    protected array $extraFields = ['phone', 'email', 'address', 'notes'];
}