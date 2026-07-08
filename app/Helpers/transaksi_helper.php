<?php
/**
 * Helper untuk perhitungan tambahan pada proses checkout:
 * - PPN (Pajak Pertambahan Nilai)
 * - Biaya Admin (tarif berjenjang)
 * - Diskon Kupon Promo
 */

if (!function_exists('hitung_ppn')) {
    /**
     * PPN dihitung 12% dari total harga pembelian (tidak termasuk ongkir).
     *
     * @param float $total_harga Total harga pembelian (belum termasuk ongkir)
     * @return float Nilai PPN
     */
    function hitung_ppn(float $total_harga): float
    {
        $tarif_ppn = 0.12;

        return $total_harga * $tarif_ppn;
    }
}

if (!function_exists('hitung_biaya_admin')) {
    /**
     * Biaya admin dihitung berdasarkan total harga pembelian dengan tarif berjenjang:
     * - <= Rp 15.000.000           : 0.5%
     * - Rp 15.000.001 - 35.000.000 : 0.7%
     * - > Rp 35.000.000            : 0.9%
     *
     * @param float $total_harga Total harga pembelian (belum termasuk ongkir)
     * @return float Nilai biaya admin
     */
    function hitung_biaya_admin(float $total_harga): float
    {
        if ($total_harga <= 15000000) {
            $tarif = 0.005;
        } elseif ($total_harga <= 35000000) {
            $tarif = 0.007;
        } else {
            $tarif = 0.009;
        }

        return $total_harga * $tarif;
    }
}

if (!function_exists('get_persentase_biaya_admin')) {
    /**
     * Mengembalikan persentase tarif biaya admin yang berlaku, untuk keperluan tampilan.
     *
     * @param float $total_harga Total harga pembelian (belum termasuk ongkir)
     * @return float Persentase tarif (contoh: 0.7 untuk 0.7%)
     */
    function get_persentase_biaya_admin(float $total_harga): float
    {
        if ($total_harga <= 15000000) {
            return 0.5;
        } elseif ($total_harga <= 35000000) {
            return 0.7;
        }

        return 0.9;
    }
}

if (!function_exists('hitung_diskon_kupon')) {
    /**
     * Diskon kupon dihitung dari total harga pembelian (sebelum PPN dan biaya admin).
     * Jika kode kupon tidak valid, diskon = 0.
     *
     * @param float       $total_harga Total harga pembelian (belum termasuk ongkir)
     * @param string|null $kupon_code  Kode kupon yang diinput pelanggan
     * @return float Nilai diskon kupon
     */
    function hitung_diskon_kupon(float $total_harga, ?string $kupon_code): float
    {
        $daftar_kupon = [
            'HEMAT20'  => 0.20,
            'HEMAT30'  => 0.30,
            'MEMBER25' => 0.25,
        ];

        $kupon_code = strtoupper(trim((string) $kupon_code));

        if ($kupon_code === '' || !isset($daftar_kupon[$kupon_code])) {
            return 0.0;
        }

        return $total_harga * $daftar_kupon[$kupon_code];
    }
}

if (!function_exists('get_persentase_kupon')) {
    /**
     * Mengembalikan persentase diskon dari kode kupon, untuk keperluan tampilan.
     * Mengembalikan 0 jika kode kupon tidak valid.
     *
     * @param string|null $kupon_code Kode kupon yang diinput pelanggan
     * @return float Persentase diskon (contoh: 20 untuk 20%)
     */
    function get_persentase_kupon(?string $kupon_code): float
    {
        $daftar_kupon = [
            'HEMAT20'  => 20,
            'HEMAT30'  => 30,
            'MEMBER25' => 25,
        ];

        $kupon_code = strtoupper(trim((string) $kupon_code));

        return $daftar_kupon[$kupon_code] ?? 0;
    }
}