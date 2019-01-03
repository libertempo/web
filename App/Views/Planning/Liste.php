<?php
/*
 * $titre
 * $message
 * $listIdUsed
 * $isHr
 * $lienModif
 * $baseURIApi
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
axios.defaults.headers.get = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Token': '<?= $_SESSION['token'] ?>',
};

const instance = axios.create({
  baseURL: '<?= $baseURIApi ?>',
  timeout: 1500
});

var vm = new Vue({
    el: '#inner-content',
    data: {
        plannings : {},
        listIdUsed : <?= json_encode($listIdUsed) ?>,
        isHr: 'true' == "<?= $isHr ? 'true' : 'false' ?>",
        lienModification : "<?= $lienModif ?>",
        statusActive : <?= \App\Models\Planning::STATUS_ACTIVE ?>,
        axios : instance
    },
    computed: {
    },
    'methods' : {
        hasPlanning : function () {
            return 0 < Object.keys(this.plannings).length;
        },
        isUsed : function (id) {
            return -1 != this.listIdUsed.indexOf(id);
        },
        linkModification : function (id) {
            return this.lienModification + '?id=' + id;
        }
    },
    created () {
        var vm = this;
        this.axios.get('/planning')
        .then((response) => {
            if (typeof response.data != 'object') {
                return;
            }
            const plannings = response.data.data;
            var activePlannings = new Array();
            for (var i = 0; i < plannings.length; ++i) {
                if (plannings[i].status === vm.statusActive) {
                    activePlannings.push(plannings[i]);
                }
            }
            vm.plannings = activePlannings;
        })
        .catch((error) => {
            console.log(error.response);
            console.error(error);
        })
    }
});
</script>
<?php
