<?php
/*
 * $titre
 * $message
 * $listIdUsed
 * $plannings
 * $isHr
 * $lienModif
 */
?>
<div id="inner-content">
    <a v-if="isHr" href="<?= ROOT_PATH ?>hr/ajout_planning" style="float:right" class="btn btn-success"><?= _('hr_ajout_planning') ?></a>
    <h1><?= $titre ?></h1>
    <?= $message ?>
    <table class="table table-hover table-responsive table-condensed table-striped">
    <thead>
        <tr><th><?= _('divers_nom_maj_1') ?></th><th style="width:10%"></th></tr>
    </thead>
    <tbody>
        <tr v-if="!hasPlanning()"><td colspan="2"><center><?= _('aucun_resultat') ?></center></td></tr>
        <tr v-for="p in plannings">
            <td>{{ p.name }}</td>
            <td>
                <form action="" method="post" accept-charset="UTF-8"
                enctype="application/x-www-form-urlencoded">
                <a title="<?= _('form_modif') ?>" :href="linkModification(p.id)"><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;
                <span v-if="isHr">
                    <span v-if="isUsed(p.id)">
                        <button title="<?= _('planning_used') ?>" type="button" class="btn btn-link disabled"><i class="fa fa-times-circle"></i></button>

                    </span>
                    <span v-else>
                        <input type="hidden" name="planning_id" :value="p.id" />
                        <input type="hidden" name="_METHOD" value="DELETE" />
                        <button type="submit" class="btn btn-link" title="<?= _('form_supprim') ?>"><i class="fa fa-times-circle"></i></button>
                    </span>
                </span>
            </form>
        </td>
        </tr>
    </tbody>
    </table>
</div>

<script>
var vm = new Vue({
    el: '#inner-content',
    data: {
        message: 'Hello Vue!',
        plannings : <?= json_encode($plannings) ?>,
        listIdUsed : <?= json_encode($listIdUsed) ?>,
        isHr: 'true' == "<?= $isHr ? 'true' : 'false' ?>",
        lienModification : "<?= $lienModif ?>"
    },
    computed: {
    },
    'methods' : {
        hasPlanning : function () {
            return 0 < this.plannings.length;
        },
        isUsed : function (id) {
            return -1 != this.listIdUsed.indexOf(id);
        },
        linkModification : function (id) {
            return this.lienModification + '&id=' + id;
        }
    }
})
</script>
