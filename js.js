array2url = function(parejas) {
    var par = [];
    for(var clave in parejas) {
        par.push(clave + '=' + encodeURI(parejas[clave]));
    }
    return par.join('&');
};

actualizar = function() {
    var id = document.getElementById('id').value;
    var multiId = document.getElementById('multiId').value;
    if(multiId !== '') {
        multiId = '&multiId=' + multiId;
    }
    var texto = document.getElementsByName('txt');
    var textoIng = document.getElementsByName('txtIng');
    var insertar = document.getElementsByName('ins');
    var updatear = document.getElementsByName('upd');
    var updatearIng = document.getElementsByName('updIng');
    // esMX
    var mx = document.getElementById('chkEsMX');
    var esMX = '';
    if(mx && mx.checked) {
        esMX = '&esMX=1';
    }
    // whowhead
    var chkRetail = document.getElementById('chkRetail').checked;
    var retail = '';
    if(chkRetail) {
        retail = '&retail=1';
    }
    // wowheadtbc
    var chkTBC = document.getElementById('chkTBC').checked;
    var tbc = '';
    if(chkTBC) {
        tbc = '&tbc=1';
    }
    // wowheadclassic
    var chkClassic = document.getElementById('chkClassic').checked;
    var classic = '';
    if(chkClassic) {
        classic = '&classic=1';
    }
    // datos + ins + upd
    var arrTxt = [];
    var arrTxtIng = [];
    var arrIns = [];
    var arrUpd = [];
    var arrUpdIng = [];
    for(var c in texto) {
        if(typeof texto[c].id !== 'undefined') {
            arrTxt[texto[c].id] = texto[c].value;
            arrTxtIng[textoIng[c].id] = textoIng[c].value;
            arrIns[insertar[c].id] = insertar[c].checked;
            arrUpd[updatear[c].id] = updatear[c].checked;
            arrUpdIng[updatearIng[c].id] = updatearIng[c].checked;
        }
    }
    var txt = array2url(arrTxt);
    var txtIng = array2url(arrTxtIng);
    var ins = array2url(arrIns);
    var upd = array2url(arrUpd);
    var updIng = array2url(arrUpdIng);
    
    var aj = new Ajax();
    aj.submit({
        url : '?accion=enviar&id=' + id + '&' + txt + '&' + txtIng + '&' + ins + '&' + upd + '&' + updIng + '&' + esMX + retail + tbc + classic + multiId,
        exito : function(res) {	
            document.getElementById('sql').innerHTML = res;
        }
    });
};

buscar = function() {
    var buscado = document.getElementById('buscado').value;
    var aj = new Ajax();
    aj.submit({
        url : '?accion=buscar&buscado=' + buscado,
        exito : function(res) {	
            document.getElementById('resulBuscar').innerHTML = res;
        }
    });
}