<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Pelanggan;
use App\Support\CodeGenerator;
use Illuminate\Validation\ValidationException;

class StokService
{
    /**
     * Validate and deduct stock for requested material line items inside an active DB transaction.
     *
     * @param  array<int, array{material_id: int, qty: int}>  $items
     * @return array<int, array{material: Material, qty: int, harga_satuan: int, subtotal: int}>
     *
     * @throws ValidationException
     */
    public function deductMaterials(array $items): array
    {
        $groupedQty = [];
        foreach ($items as $item) {
            $materialId = (int) $item['material_id'];
            $groupedQty[$materialId] = ($groupedQty[$materialId] ?? 0) + (int) $item['qty'];
        }

        $materials = Material::query()
            ->whereIn('id', array_keys($groupedQty))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($groupedQty as $materialId => $totalNeeded) {
            $material = $materials->get($materialId);

            if ($material === null) {
                throw ValidationException::withMessages([
                    'items' => 'Material yang dipilih tidak ditemukan.',
                ]);
            }

            if ($material->stok_sisa < $totalNeeded) {
                throw ValidationException::withMessages([
                    'items' => "Stok material \"{$material->nama_barang}\" tidak mencukupi (tersedia: {$material->stok_sisa} {$material->satuan->value}, diminta: {$totalNeeded}).",
                ]);
            }
        }

        $prepared = [];
        foreach ($groupedQty as $materialId => $qty) {
            $material = $materials->get($materialId);
            $hargaSatuan = $material->harga_jual;
            $subtotal = $qty * $hargaSatuan;

            $material->decrement('stok_sisa', $qty);

            $prepared[] = [
                'material' => $material,
                'qty' => $qty,
                'harga_satuan' => $hargaSatuan,
                'subtotal' => $subtotal,
            ];
        }

        return $prepared;
    }

    /**
     * Resolve an existing customer or create a new one from inline form fields.
     */
    public function resolvePelanggan(?int $pelangganId, ?string $namaBaru, ?string $teleponBaru, ?string $alamatBaru): ?int
    {
        if ($pelangganId !== null) {
            return $pelangganId;
        }

        if ($namaBaru !== null && trim($namaBaru) !== '') {
            $pelanggan = Pelanggan::create([
                'kode_pelanggan' => CodeGenerator::next(Pelanggan::class, 'kode_pelanggan', 'PLG', 4),
                'nama_pelanggan' => trim($namaBaru),
                'no_telepon' => $teleponBaru !== null && trim($teleponBaru) !== '' ? trim($teleponBaru) : null,
                'alamat' => $alamatBaru !== null && trim($alamatBaru) !== '' ? trim($alamatBaru) : null,
            ]);

            return $pelanggan->id;
        }

        return null;
    }
}

