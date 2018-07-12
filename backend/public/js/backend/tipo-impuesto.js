urlEliminar='/tipo-impuesto/eliminar';

$(document).ready(function(){
    cargaGridfrmTipoImpuesto();
});

function cargaGridfrmTipoImpuesto()
{
    var filtro="/tipo-impuesto/grid";
    if($("#fabreviatura").val()!="")       filtro+="/abreviatura/"+$("#fabreviatura").val();
    if($("#fdescripcion").val()!="")       filtro+="/descripcion/"+$("#fdescripcion").val();
    if($("#fstatus").val()!="")     filtro+="/status/"+$("#fstatus").val();
    
        $("#flexigrid").flexigrid({
        url: filtro,
        dataType: "xml",
        colModel: [
            {display: "Abreviatura",           name:"abreviatura",       width: 250, sortable: true, align: "center"},
            {display: "Descripcion",           name:"descripcion",       width: 414, sortable: true, align: "center"},
        ],
        sortname: "abreviatura"
        ,sortorder: "asc"
        ,usepager: true
        ,useRp: false
        ,singleSelect: true
        ,resizable: false
        ,showToggleBtn: false
        ,rp: 10
        ,width: "auto"
        ,height: 400
        ,onSuccess: function(){
            $("#flexigrid tr").bind('click',function(){
                
                //obtenemos el id del impuesto
                var idTipoImpuesto = $(this).find(".registro").attr("rel").valueOf();
                $("#id").val(idTipoImpuesto);
                console.log(idTipoImpuesto);
                obtenerTipoImpuesto(idTipoImpuesto);

            });
        }
    });
}

function obtenerTipoImpuesto(id){
    
    $.ajax({
        type: "POST"
        ,url: "/tipo-impuesto/obtener"
        ,dataType: "json"
        ,processData:true
        ,data: {id:id }
        ,success: function(re){
            console.log("Servidor: ");
            console.log(re);
            if(re!='' && re!=undefined && re.error==''){
                $("#abreviatura").val(re.abreviatura);
                $("#descripcion").val(re.descripcion);
                $("#status").val(re.status).change();
                $("#eliminar").attr("onclick","eliminar('"+re.id+"','"+re.status+"','frmTipoImpuesto')");
                    
            }

            else{
                _mensaje("#_mensaje-1", re.error);
                if(re.filtrado!='' && re.filtrado!=undefined){
                    if(peticion=="eliminar-servicios"){
                        limpiarServicio('0');
                    }
                }  
            }
        }//success
        ,error: function(error){
            _mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado, int&eacute;ntelo de nuevo");
        } //error
    }) 
}

function limpiarfrmTipoImpuesto()
{
    $("#frmTipoImpuesto")[0].reset();
    $("#eliminar").attr("onclick","eliminar('','1')");
    $("#id").val('');
}