urlEliminar='/tipo-empresa/eliminar';

$(document).ready(function(){
    cargaGridfrmTipoEmpresa();
});

function cargaGridfrmTipoEmpresa()
{
    var filtro="/tipo-empresa/grid";
    if($("#ftipo_empresa").val()!="")       filtro+="/tipo_empresa/"+$("#ftipo_empresa").val();
    if($("#ftipo_calculo").val()!="")       filtro+="/tipo_calculo/"+$("#ftipo_calculo").val();
    
        $("#flexigrid").flexigrid({
        url: filtro,
        dataType: "xml",
        colModel: [
            {display: "Tipo de empresa",           name:"tipo_empresa",       width: 300, sortable: true, align: "center"},
            {display: "Tipo de calculo",           name:"tipo_calculo",       width: 265, sortable: true, align: "center"},
            {display: "Estatus",           name:"status",       width: 100, sortable: true, align: "center"},
        ],
        sortname: "tipo_empresa"
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
                
                //obtenemos el id de la empresa
                var idTipoEmpresa = $(this).find(".registro").attr("rel").valueOf();
                $("#id").val(idTipoEmpresa);
                console.log(idTipoEmpresa);
                obtenerTipoEmpresa(idTipoEmpresa);

            });
        }
    });
}

function obtenerTipoEmpresa(id){
    
    $.ajax({
        type: "POST"
        ,url: "/tipo-empresa/obtener"
        ,dataType: "json"
        ,processData:true
        ,data: {id:id }
        ,success: function(re){
            console.log("Servidor: ");
            console.log(re);
            if(re!='' && re!=undefined && re.error==''){

                //$("input[id=select_tipo_calculo1]").attr("checked", false);
                //$("input[id=select_tipo_calculo2]").attr("checked", false);
                $("#tipo_empresa").val(re.tipo_empresa);
                //$("#tipo_calculo").val(re.tipo_calculo);
                if (re.tipo_calculo == 1) {
                        //$("input[id=select_tipo_calculo1]").attr("checked",true);
                        document.getElementById('select_tipo_calculo1').checked=true;
                        //$("input[id=select_tipo_calculo2]").attr("checked",false);
                    }else if(re.tipo_calculo == 2) {
                        //$("input[id=select_tipo_calculo2]").attr("checked",true);
                        document.getElementById('select_tipo_calculo2').checked=true;
                        //$("input[id=select_tipo_calculo1]").attr("checked",false);
                    }
                $("#status").val(re.status).change();
                $("#eliminar").attr("onclick","eliminar('"+re.id+"','"+re.status+"','frmTipoEmpresa')");
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

function limpiarfrmTipoEmpresa()
{
    $("#frmTipoEmpresa")[0].reset();
    $("input[id=select_tipo_calculo1]").attr("checked",false);
    $("input[id=select_tipo_calculo2]").attr("checked",false);
    $("#eliminar").attr("onclick","eliminar('','1')");
    $("#id").val('');
}