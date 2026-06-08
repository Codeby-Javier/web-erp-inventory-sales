<?php

declare(strict_types=1);

final class CategoryHandler extends MasterDataHandler
{
    protected string $table = 'product_categories';
    protected string $title = 'Kategori';
    protected string $viewFolder = 'category';
}