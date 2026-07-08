<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\RajaOngkirService;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;  

class TransaksiController extends BaseController
{
    protected $cart;
    protected $transactionModel;
    protected $transactionDetailModel;

    public function __construct()
    {
        helper(['number', 'form', 'transaksi']);
        $this->cart = service('cart');
        $this->transactionModel = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel(); 
    }

    public function index()
    {  
        $data = [
            'items' => $this->cart->contents(), 
            'total' => $this->cart->total() 
        ];

        return view('v_keranjang', $data);
    }

    public function cart_add()
    {
	    $this->cart->insert([
	        'id'      => $this->request->getPost('id'),
	        'qty'     => 1,
	        'price'   => $this->request->getPost('harga'),
	        'name'    => $this->request->getPost('nama'),
	        'options' => [
	            'foto' => $this->request->getPost('foto')
	        ]
	    ]);
	
	    session()->setFlashdata(
	        'success',
	        'Produk berhasil ditambahkan ke keranjang. 
	        <a href="' . base_url('keranjang') . '">Lihat</a>'
	    );
	
	    return redirect()->to(base_url('/'));
    } 

    public function cart_edit()
    {
        $i = 1;
        foreach ($this->cart->contents() as $item) {
            $qty = $this->request->getPost('qty' . $i++);

            $this->cart->update([
                'rowid' => $item['rowid'],
                'qty'   => $qty
            ]);
        }

        session()->setFlashdata(
            'success',
            'Keranjang berhasil diperbarui'
        );

        return redirect()->to(base_url('keranjang'));
    }

    public function cart_delete($rowid)
    {
        $this->cart->remove($rowid);

        session()->setFlashdata(
            'success',
            'Produk berhasil dihapus dari keranjang'
        );

        return redirect()->to(base_url('keranjang'));
    }

    public function cart_clear()
    {
        $this->cart->destroy();

        session()->setFlashdata(
            'success',
            'Keranjang berhasil dikosongkan'
        );

        return redirect()->to(base_url('keranjang'));
    }

    public function checkout()
    {
        $total     = $this->cart->total();
        $kuponCode = $this->request->getGet('kupon_code');

        $diskonKupon = hitung_diskon_kupon($total, $kuponCode);
        $ppn         = hitung_ppn($total);
        $biayaAdmin  = hitung_biaya_admin($total);

        $data = [
            'items'          => $this->cart->contents(),
            'total'          => $total,
            'kupon_code'     => $kuponCode,
            'diskon_kupon'   => $diskonKupon,
            'ppn'            => $ppn,
            'biaya_admin'    => $biayaAdmin,
            'persen_admin'   => get_persentase_biaya_admin($total),
            'persen_kupon'   => get_persentase_kupon($kuponCode),
        ];

        return view('v_checkout', $data);
    }

    /**
     * Endpoint AJAX untuk menghitung ulang rincian biaya (PPN, biaya admin,
     * diskon kupon) setiap kali pelanggan mengubah kode kupon di halaman checkout.
     */
    public function hitungRincian()
    {
        $total     = $this->cart->total();
        $kuponCode = $this->request->getGet('kupon_code');

        $diskonKupon = hitung_diskon_kupon($total, $kuponCode);
        $ppn         = hitung_ppn($total);
        $biayaAdmin  = hitung_biaya_admin($total);
        $kuponValid  = $diskonKupon > 0 || strtoupper(trim((string) $kuponCode)) === '';

        return $this->response->setJSON([
            'total'         => $total,
            'diskon_kupon'  => $diskonKupon,
            'persen_kupon'  => get_persentase_kupon($kuponCode),
            'ppn'           => $ppn,
            'biaya_admin'   => $biayaAdmin,
            'persen_admin'  => get_persentase_biaya_admin($total),
            'kupon_valid'   => $kuponValid,
        ]);
    }

    public function destinations()
    {
        $search = $this->request->getGet('q'); 

        $service = new RajaOngkirService();
        $response = $service->getDestination($search);

        $results = [];
        $data = $response['data'] ?? [];

        foreach ($data as $item) {
            $results[] = [
                'id'   => $item['id'],
                'text' => $item['label']
            ];
        }
        return $this->response->setJSON([
            'results' => $results
        ]);
    }

    public function costs()
    {
        $origin = '64999';
        $destination = $this->request->getGet('destination');
        $weight = '1000';
        $courier = 'jne'; 

        $service = new RajaOngkirService();
        $response = $service->getCost($origin, $destination, $weight, $courier);

        $results = [];
        $data = $response['data'] ?? [];

        foreach ($data as $item) {
            $results[] = [
                'service'     => $item['service'],
                'description' => $item['description'],
                'cost'        => $item['cost'],
                'etd'         => $item['etd']
            ];
        }

        return $this->response->setJSON($results);
    }

    public function buy()
    { 
        $cartItems = $this->cart->contents();

        if (empty($cartItems)) {
            return redirect()->back();
        }

        $db = \Config\Database::connect();
        $db->transStart(); 

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['qty'] * $item['price'];
        }

        $ongkir     = (int) $this->request->getPost('ongkir');
        $kuponCode  = $this->request->getPost('kupon_code');

        // Hitung komponen tambahan (dihitung dari total harga pembelian, tidak termasuk ongkir)
        $diskonKupon = hitung_diskon_kupon($subtotal, $kuponCode);
        $ppn         = hitung_ppn($subtotal);
        $biayaAdmin  = hitung_biaya_admin($subtotal);

        // Simpan kode kupon hanya jika valid (diskon > 0), selain itu null
        $kuponCodeTersimpan = $diskonKupon > 0 ? strtoupper(trim($kuponCode)) : null;

        $totalHarga = $subtotal - $diskonKupon + $ppn + $biayaAdmin + $ongkir;

        $transaction = [
            'username'     => $this->request->getPost('username'),
            'alamat'       => $this->request->getPost('alamat'),
            'ongkir'       => $ongkir,
            'total_harga'  => $totalHarga,
            'status'       => 0,
            'ppn'          => $ppn,
            'biaya_admin'  => $biayaAdmin,
            'kupon_code'   => $kuponCodeTersimpan,
            'diskon_kupon' => $diskonKupon,
        ];

        // insert transaction
        if (!$this->transactionModel->insert($transaction)) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Gagal membuat transaksi');
        }

        $transactionId = $this->transactionModel->getInsertID();

        // insert transaction detail
        foreach ($cartItems as $item) {
            $this->transactionDetailModel->insert([
                'transaction_id' => $transactionId,
                'product_id'     => $item['id'],
                'jumlah'         => $item['qty'],
                'diskon'         => 0,
                'subtotal_harga' => $item['qty'] * $item['price'] 
            ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->with('error', 'Gagal membuat transaksi');
        }

		//hapus session keranjang belanja 
        $this->cart->destroy();
        return redirect()->to(base_url());
    }

    public function history()
    {
        $username = session()->get('username'); 
 
        $transactions = $this->transactionModel->where('username', $username)->findAll();
        $transactionIds = array_column($transactions, 'id');

        $products = $this->transactionDetailModel->getProductsByTransactionIds($transactionIds);

        $data = [
            'username'      => $username,
            'transactions'  => $transactions,
            'products'      => $products
        ]; 

        return view('v_history', $data);
    }
}