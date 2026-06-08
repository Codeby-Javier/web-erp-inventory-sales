<?php

declare(strict_types=1);

final class PaymentHandler extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $this->json(['message' => 'Gunakan endpoint pembayaran melalui sales detail.']);
    }
}