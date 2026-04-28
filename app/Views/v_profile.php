<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>

<div class="pagetitle">
    <h1>Data Tables</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Home</a></li>
            <li class="breadcrumb-item active">Profile</li>
        </ol>
    </nav>
</div>

<section class="section profile">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Profile</h5>

                    <div class="card mt-2">
                        <div class="card-body">
                            <h5 class="card-title">Profile Information</h5>

                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td style="width: 150px; color: #4154f1; font-weight: 600;">Username</td>
                                        <td><?= esc($username) ?></td>
                                    </tr>
                                    <tr>
                                        <td style="color: #4154f1; font-weight: 600;">Role</td>
                                        <td>
                                            <span class="badge bg-danger"><?= esc($role) ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #4154f1; font-weight: 600;">Email</td>
                                        <td><?= esc($email) ?></td>
                                    </tr>
                                    <tr>
                                        <td style="color: #4154f1; font-weight: 600;">Login Time</td>
                                        <td><?= esc($login_time) ?></td>
                                    </tr>
                                    <tr>
                                        <td style="color: #4154f1; font-weight: 600;">Status</td>
                                        <td>
                                            <?php if ($isLoggedIn): ?>
                                                <span class="badge bg-success">&#10003; Sudah Login</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Belum Login</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<?php $this->endSection(); ?>