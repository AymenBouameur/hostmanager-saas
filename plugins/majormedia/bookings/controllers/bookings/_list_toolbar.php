<div data-control="toolbar">
    <a href="<?= Backend::url('majormedia/bookings/bookings/create') ?>" class="btn btn-primary oc-icon-plus">
        <?= e(trans('backend::lang.form.create')) ?>
    </a>
    <button class="btn btn-default oc-icon-trash-o" data-request="onDelete"
        data-request-confirm="<?= e(trans('backend::lang.list.delete_selected_confirm')) ?>" data-list-checked-trigger
        data-list-checked-request data-stripe-load-indicator>
        <?= e(trans('backend::lang.list.delete_selected')) ?>
    </button>

    <button class="btn btn-default oc-icon-refresh" data-control="popup" data-handler="onShowSyncBookingsModal"
        data-size="small">
        <?= e('Synchroniser les réservations depuis l\'API') ?>
    </button>

</div>