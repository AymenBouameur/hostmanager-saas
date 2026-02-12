<!-- Popup Mois -->
<div id="monthPopup"
    style="display: none; position: fixed; top: 30%; left: 50%; transform: translateX(-50%); background: #fff; padding: 20px; border: 1px solid #ddd; z-index: 9999; box-shadow: 0 0 10px rgba(0,0,0,0.3);">
    <h4>Choisir le mois</h4>
    <select id="popup-month" class="form-control" style="width: 100%; margin-bottom: 15px;">
        <option value="0">Sélectionner un mois</option>
        <option value="1">Janvier</option>
        <option value="2">Février</option>
        <option value="3">Mars</option>
        <option value="4">Avril</option>
        <option value="5">Mai</option>
        <option value="6">Juin</option>
        <option value="7">Juillet</option>
        <option value="8">Août</option>
        <option value="9">Septembre</option>
        <option value="10">Octobre</option>
        <option value="11">Novembre</option>
        <option value="12">Décembre</option>
    </select>

    <div style="text-align: right;">
        <button class="btn btn-primary" id="validateMonth">Valider</button>
        <button class="btn btn-default" onclick="$('#monthPopup').hide();">Annuler</button>
    </div>
</div>

<!-- Toolbar -->
<div data-control="toolbar">
    <a href="<?= Backend::url('majormedia/listings/listings/create') ?>" class="btn btn-primary oc-icon-plus">
        <?= e(trans('backend::lang.form.create')) ?>
    </a>
    <button class="btn btn-default oc-icon-trash-o" data-request="onDelete"
        data-request-confirm="<?= e(trans('backend::lang.list.delete_selected_confirm')) ?>" data-list-checked-trigger
        data-list-checked-request data-stripe-load-indicator>
        <?= e(trans('backend::lang.list.delete_selected')) ?>
    </button>
    <?php if (BackendAuth::userHasAccess('majormedia.listings::listings.sync')): ?>
        <button class="btn btn-default oc-icon-refresh" data-request="onSyncProperties" data-stripe-load-indicator>
            <?= e('Synchroniser via API') ?>
        </button>
    <?php endif ?>

    <div class="btn-group">
        <?php if (BackendAuth::userHasAccess('majormedia.listings::listings.export_csv')): ?>
            <button class="btn btn-default oc-icon-export" data-control="popup" data-handler="onChooseMonth">
                Exporter synthèse admin </button>
            <button class="btn btn-default oc-icon-upload" data-control="popup" data-handler="onChooseMonth"
                data-list-checked-request data-list-checked-trigger data-stripe-load-indicator>
                <?= e('Exporter synthèse admin') ?>
            </button>
        <?php endif ?>
    </div>

    <div class="btn-group">
        <button class="btn btn-default oc-icon-file-pdf-o" data-control="popup" data-handler="onSelectMonthModal"
            data-request-data="type:1">
            Générer Relevé
        </button>

        <button class="btn btn-default oc-icon-file-pdf-o" data-control="popup" data-handler="onSelectMonthModal"
            data-request-data="type:2">
            Générer Facture
        </button>
    </div>

    <div class="btn-group">
        <button class="btn btn-success oc-icon-check-circle" data-control="popup" data-handler="onSelectMonthModal"
            data-request-data="type:3">
            Activer Relevé
        </button>
        <button class="btn btn-danger oc-icon-times-circle" data-control="popup" data-handler="onSelectMonthModal"
            data-request-data="type:4">
            Désactiver Relevé
        </button>
    </div>

</div>

<script>
    var pdfMode = '';

    function openMonthPopup(mode) {
        pdfMode = mode;
        $('#popup-month').val(0);
        $('#monthPopup').show();
    }
</script>