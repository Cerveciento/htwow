// Clase Ajax
Ajax = function(obj) {
    // Referencia a si mismo
    var me = this;
    // Propiedades que se sobreescriben con el objeto de configuración
    me.asincrono = true;
    me.xml = false;
    me.error404 = 'Fichero no encontrado';
    // métodos que se sobreescriben con el objeto de configuración
    me.exito = false;
    me.cargando = false;
    me.error = false;
    // Sobreescritura
    if(typeof(obj == 'object')) {
        for(var o in obj) {
            me[o] = obj[o];
        }
    }

    me.objAjax = window.XMLHttpRequest ? new XMLHttpRequest() : window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : false;
    me.fn = function(fn){
        if(typeof fn == 'string') return new Function(fn);
        if(typeof fn == 'function') return fn;
        if(typeof fn == 'undefined') return new Function();
        return fn;
    };
    me.abortar= function() {
        if(me.trabajando) {
            me.objAjax.abort();	
            me.trabajando = false;
        }
    };
    me.submit = function(obj) {
        if(typeof(obj === 'object')) {
            var sep = '';
            for(var o in obj) {
                me[o] = obj[o];
            }
        }
        if(me.objAjax) {
            me.objAjax.onreadystatechange = function() {
                if(me.objAjax.readyState == 4) {
                    me.trabajando = false;
                    if(me.objAjax.status == 200 || me.objAjax.status == 0) {
                        var respuesta = me.xml ? me.objAjax.responseXml : me.objAjax.responseText;
                        if(me.exito) {
                            me.fn(me.exito).call(this, respuesta, me);
                        } else {
                            me.fn(obj.exito).call(this, respuesta, me);
                        }
                    } else {
                        if(me.error) {	
                            me.fn(me.error).call(this, me.error404);
                        } else {
                            me.fn(obj.error).call(this, me.error404);
                        }
                    }
                } else {		// readyState != 4
                    me.trabajando = true;
                    if(me.cargando) {
                        me.fn(me.cargando).call(this, me);
                    } else {
                        me.fn(obj.cargando).call(this, me);
                    }
                }
            };

            me.objAjax.open("GET", obj.url, me.asincrono);
            me.objAjax.send(null);
        } else obj.error.call(this, me);
    };
    
    return me;
};
/*
// Ejemplo de uso
var aj = new Ajax(obj);
aj.submit({
    url : 'laQueSea.html',
    exito : function(res, obj) {
        document.getElementById('p2').innerHTML = res;
    },
    cargando : function() { console.log('Cargando...'); },
    error : function(err) { console.log(err); }
});
*/
