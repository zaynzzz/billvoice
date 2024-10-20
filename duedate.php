<div class="row">
            <?php if (count($all_invoices) > 0): ?>
                <?php foreach ($all_invoices as $invoice): ?>
                    <?php
                    // Tentukan status berdasarkan due date dan status
                    $invoice_status = '';
                    $card_class = '';

                    $today = date('Y-m-d'); // Tanggal hari ini
                    $due_date = $invoice['invoice_due_date'];
                    $days_until_due = (strtotime($due_date) - strtotime($today)) / (60 * 60 * 24); // Hitung selisih hari

                    if ($invoice['status'] == 'paid') {
                        $invoice_status = 'Paid';
                        $card_class = 'card-header-paid';
                    } elseif ($due_date < $today && $invoice['status'] == 'open') {
                        $invoice_status = 'Unpaid (Overdue)';
                        $card_class = 'card-header-due';
                    } elseif ($due_date == $today && $invoice['status'] == 'open') {
                        $invoice_status = 'Due Today';
                        $card_class = 'card-header-due';
                    } elseif ($days_until_due <= 7 && $days_until_due > 0 && $invoice['status'] == 'open') {
                        // Jika due date dalam 7 hari dari sekarang, anggap sebagai "Warning"
                        $invoice_status = 'Warning: Due in ' . $days_until_due . ' days';
                        $card_class = 'card-header-warning';
                    } elseif ($days_until_due > 7 && $invoice['status'] == 'open') {
                        $invoice_status = 'Upcoming';
                        $card_class = 'card-header-upcoming';
                    }
                    ?>
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="card-header <?= $card_class; ?>">
                                <strong>Status: <?= $invoice_status; ?></strong><br>
                                <small>Due Date: <?= $invoice['invoice_due_date']; ?></small>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">Customer: <?= $invoice['custom_email']; ?></h5>
                                <p class="card-text">Invoice: <?= $invoice['invoice']; ?></p>
                                <p class="card-text">Total: Rp. <?= number_format($invoice['total'], 0, ',', '.'); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No invoices found.</p>
            <?php endif; ?>
        </div>