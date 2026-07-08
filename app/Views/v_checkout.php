<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-lg-6">
        <?= form_open('buy', 'class="row g-3"') ?>

        <?= form_hidden('username', session()->get('username')) ?>

        <div class="col-12">
            <?= form_label('Nama', 'nama', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'     => 'nama',
                'id'       => 'nama',
                'class'    => 'form-control',
                'value'    => session()->get('username'),
                'readonly' => true]) ?>
        </div>
        <div class="col-12">
            <?= form_label('Alamat', 'alamat', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'  => 'alamat',
                'id'    => 'alamat',
                'class' => 'form-control']) ?>
        </div> 
        <div class="col-12"> 
            <?= form_label('Kelurahan', 'kelurahan', ['class' => 'form-label']) ?>
            <?= form_dropdown('kelurahan', [], '', ['id' => 'kelurahan', 'class' => 'form-control']) ?>
        </div>
        <div class="col-12"> 
            <?= form_label('Layanan', 'layanan', ['class' => 'form-label']) ?> 
            <?= form_dropdown('layanan', [], '', ['id' => 'layanan', 'class' => 'form-control']) ?>
        </div>
        <div class="col-12">
            <?= form_label('Ongkir', 'ongkir', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'     => 'ongkir',
                'id'       => 'ongkir',
                'class'    => 'form-control',
                'readonly' => true]) ?>
        </div>
        <div class="col-12">
            <?= form_label('Kode Kupon', 'kupon_code', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'        => 'kupon_code',
                'id'          => 'kupon_code',
                'class'       => 'form-control',
                'placeholder' => 'Contoh: HEMAT20',
                'value'       => old('kupon_code', $kupon_code ?? '')]) ?>
            <small class="text-muted">Tersedia: HEMAT20, HEMAT30, MEMBER25</small>
            <div id="kupon-feedback" class="mt-1"></div>
        </div>
        <div class="col-12">
            <?= form_submit(
                'submit',
                'Buat Pesanan',
                ['class' => 'btn btn-primary']) ?>
        </div>

        <?= form_close() ?> 
    </div>
    <div class="col-lg-6">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Harga</th>
                    <th scope="col">Jumlah</th>
                    <th scope="col">Sub Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (!empty($items)) :
                    foreach ($items as $index => $item) :
                ?>
                    <tr>
                        <td><?= $item['name'] ?></td>
                        <td><?= number_to_currency($item['price'], 'IDR') ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td><?= number_to_currency($item['price'] * $item['qty'], 'IDR') ?></td>
                    </tr>
                <?php
                    endforeach;
                endif;
                ?>
                <tr>
                    <td colspan="2"></td>
                    <td>Subtotal</td>
                    <td><?= number_to_currency($total, 'IDR') ?></td>
                </tr>
                <tr class="text-danger">
                    <td colspan="2"></td>
                    <td>Diskon Kupon <span id="persen-kupon-label"><?= $persen_kupon > 0 ? '(' . $persen_kupon . '%)' : '' ?></span></td>
                    <td>-<span id="diskon-kupon"><?= number_to_currency($diskon_kupon, 'IDR') ?></span></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>PPN (12%)</td>
                    <td><span id="ppn"><?= number_to_currency($ppn, 'IDR') ?></span></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>Biaya Admin <span id="persen-admin-label">(<?= $persen_admin ?>%)</span></td>
                    <td><span id="biaya-admin"><?= number_to_currency($biaya_admin, 'IDR') ?></span></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>Subtotal (+PPN+Admin-Kupon)</td>
                    <td><span id="subtotal-akhir"><?= number_to_currency($total - $diskon_kupon + $ppn + $biaya_admin, 'IDR') ?></span></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>Grand Total (+ Ongkir)</td>
                    <td><span id="total"><?= number_to_currency($total - $diskon_kupon + $ppn + $biaya_admin, 'IDR') ?></span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('script') ?>
<script>
$(document).ready(function() {
    let ongkir = 0;
    let subtotal = <?= $total ?>;
    let diskonKupon = <?= $diskon_kupon ?>;
    let ppn = <?= $ppn ?>;
    let biayaAdmin = <?= $biaya_admin ?>;

    hitungTotal();

    function formatRupiah(angka) {
        return `IDR ${Math.round(angka).toLocaleString('id-ID')}`;
    }

    function hitungTotal() {
        let subtotalAkhir = subtotal - diskonKupon + ppn + biayaAdmin;
        let total = subtotalAkhir + ongkir;

        $("#ongkir").val(ongkir);
        $("#diskon-kupon").text(formatRupiah(diskonKupon));
        $("#ppn").text(formatRupiah(ppn));
        $("#biaya-admin").text(formatRupiah(biayaAdmin));
        $("#subtotal-akhir").text(formatRupiah(subtotalAkhir));
        $("#total").text(formatRupiah(total));
        $("#total_harga").val(total);
    }

    let kuponTimer;
    $("#kupon_code").on('keyup', function() {
        clearTimeout(kuponTimer);
        let kode = $(this).val();

        kuponTimer = setTimeout(function() {
            $.ajax({
                url: "<?= site_url('ajax/rincian') ?>",
                dataType: "json",
                data: { kupon_code: kode },
                success: function(data) {
                    diskonKupon = parseFloat(data.diskon_kupon);
                    ppn = parseFloat(data.ppn);
                    biayaAdmin = parseFloat(data.biaya_admin);

                    if (data.persen_kupon > 0) {
                        $("#persen-kupon-label").text(`(${data.persen_kupon}%)`);
                        $("#kupon-feedback").html('<small class="text-success">Kupon valid</small>');
                    } else if (kode.trim() === '') {
                        $("#persen-kupon-label").text('');
                        $("#kupon-feedback").html('');
                    } else {
                        $("#persen-kupon-label").text('');
                        $("#kupon-feedback").html('<small class="text-danger">Kode kupon tidak valid</small>');
                    }

                    $("#persen-admin-label").text(`(${data.persen_admin}%)`);
                    hitungTotal();
                }
            });
        }, 400);
    });

	$('#kelurahan').select2({
	    placeholder: 'Cari daerah tujuan',
	    minimumInputLength: 3,
        ajax: {
            url: '<?= site_url('ajax/destinations') ?>',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return data;
            },
            cache: true
        } 
	});

    $("#kelurahan").on('change', function () {
        let id_kelurahan = $(this).val();

        $("#layanan").empty();
        ongkir = 0;
        hitungTotal(); 

        $.ajax({
            url: "<?= site_url('ajax/costs') ?>", 
            dataType: "json",
            data: {
                destination: id_kelurahan
            },
            success: function (data) { 
                data.forEach(function (item) {
                    $("#layanan").append(
                        $('<option>', {
                            value: item.cost,
                            text: `${item.description} (${item.service}) : estimasi ${item.etd}`
                        })
                    );
                });
            }
        });
    });

    $("#layanan").on('change', function() {
        ongkir = parseInt($(this).val());
        hitungTotal();
    }); 
});
</script>
<?= $this->endSection() ?>