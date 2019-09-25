<?php declare(strict_types = 1);

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//boucle...
?>
<h1><?= $titre ?></h1>
<div id="inner-content">
<div class="wrapper" v-for="s in soldes">
    <h4 class="progress-label">{{ getLibelle(s) }}</h4>
    <div class="progress">
        <div role="progressbar" class="progress-bar progress-bar-success" :style="getWidthRestant(s)">Restants : {{ getRestant(s) }}</div>
        <div role="progressbar" class="progress-bar progress-bar" :style="getWidthReliquat(s)">Reliquats : {{ getReliquat(s) }}</div>
        <div role="progressbar" class="progress-bar progress-bar" :style="getWidthConsomme(s)">Consommés : {{ getConsomme(s) }}</div>
    </div>
    <hr>
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
        soldes : {},
        axios : instance
    },
    computed: {
    },
    'methods' : {
        getLibelle : function (solde) {
            return solde['type_absence'];
        },
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
            vm.soldes = soldes;
        })
        .catch((error) => {
            console.log(error.response);
            console.error(error);
        })
    },
    updated () {
    }
});
</script>
