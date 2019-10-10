<?php declare(strict_types = 1);

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<div id="inner-content">
    <div id="loader-bar" class="progress">
        <div class="progress-bar progress-bar-striped active" role="progressbar"
        aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">
        </div>
    </div>
    <div class="wrapper">
        <div class="col-sm-12" v-for="s in soldesCongeNatif"> 
            <div class="col-sm-2">
                <h4 class="progress-label">{{ s.libelle }}</h4>
            </div>
            <div class="col-sm-10">
                <div class="progress">
                    <div role="progressbar" class="progress-bar progress-bar-success" :style="getWidthRestant(s)">Restants : {{ getRestant(s) }}</div>
                    <div role="progressbar" class="progress-bar progress-bar" :style="getWidthReliquat(s)">Reliquats : {{ getReliquat(s) }}</div>
                    <div role="progressbar" class="progress-bar progress-bar" :style="getWidthConsomme(s)">Consommés : {{ getConsomme(s) }}</div>
                </div>
            </div>
        </div>
        <hr>
        <a href="#plus" data-toggle="collapse">Plus...</a>
        <div id="plus" class="collapse">
            <div class="col-sm-12" v-for="s in soldesConge"> 
                <div class="col-sm-2">
                    <h4 class="progress-label">{{ s.libelle }}</h4>
                </div>
                <div class="col-sm-10">
                    <div class="progress">
                        <div role="progressbar" class="progress-bar progress-bar-success" :style="getWidthRestant(s)">Restants : {{ getRestant(s) }}</div>
                        <div role="progressbar" class="progress-bar progress-bar" :style="getWidthReliquat(s)">Reliquats : {{ getReliquat(s) }}</div>
                        <div role="progressbar" class="progress-bar progress-bar" :style="getWidthConsomme(s)">Consommés : {{ getConsomme(s) }}</div>
                    </div>
                </div>
            </div>
           <div class="col-sm-12" v-for="s in soldesAutre"> 
                <div class="col-sm-2">
                    <h4 class="progress-label">{{ s.libelle }}</h4>
                </div>
                <div class="col-sm-10">
                    <div class="progress">
                        <div role="progressbar" class="progress-bar progress-bar-success" style="width:100%">{{ getRestant(s) }} Jours</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
        types : {},
        soldesCongeNatif : {},
        soldesConge : {},
        soldesAutre : {},
        axios : instance
    },
    computed: {
    },
    'methods' : {
        getRestant : function (solde) {
            return solde['solde'];
        },
        getWidthRestant : function (solde) {
            return "width:" + ((solde['solde'] - solde['reliquat']) / solde['solde_annuel'] * 100) + "%";
        },
        getReliquat : function (solde) {
            return solde['reliquat'];
        },
        getWidthReliquat : function (solde) {
            return "width:" + (solde['reliquat'] / solde['solde_annuel'] * 100) + "%";
        },
        getConsomme : function (solde) {
            return (solde['solde_annuel'] - solde['solde']);
        },
        getWidthConsomme : function (solde) {
            return "width:" + ((solde['solde_annuel'] - solde['solde']) / solde['solde_annuel'] * 100) + "%; background-color:gray !important;";
        }
    },
    created () {
        var vm = this;
        this.axios.get('/absence/type')
        .then((response) => {
            if (typeof response.data != 'object') {
                return;
            }
            const types = response.data.data;
            vm.types = types;
        })
        .catch((error) => {
            console.log(error.response);
            console.error(error);
        }),
        this.axios.get('/employe/me/solde')
        .then((response) => {
            if (typeof response.data != 'object') {
                return;
            }
            const soldes = response.data.data;
            var soldesLibelleConges = new Array();
            var soldesLibelleNatif = new Array();
            var soldesLibelleAutre = new Array();
            for (var i = 0; i < soldes.length; ++i) {
                var solde = soldes[i];
                for (var j = 0; j < vm.types.length; ++j) {
                    var type = vm.types[j];
                    if (solde.type_absence === type.id) {
                        solde.libelle = type.libelle;
                        if (type.typeNatif === true && type.type === 'conges') {
                            soldesLibelleNatif.push(solde);
                        } else if (type.type === 'conges') {
                            soldesLibelleConges.push(solde);
                        } else {
                            soldesLibelleAutre.push(solde);
                        }
                    }
                }
            }
        document.getElementById('loader-bar').classList.add('hidden');
        vm.soldesCongeNatif = soldesLibelleNatif;
        vm.soldesConge = soldesLibelleConges;
        vm.soldesAutre = soldesLibelleAutre;
        })
        .catch((error) => {
            console.log(error.response);
            console.error(error);
        });
    },
    updated () {
    }
});
</script>
