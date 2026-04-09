/* FUNCIONES GLOBALES JS PERSONALIZADAS DEL SISTEMA */

//activa el tooltips de bootstrap
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
});

//activa el Switch de bootstrap
$("[name='roles[]'").bootstrapSwitch();

//activa el select2 global (excluye los que tienen clase no-select2)
$('select:not(.no-select2)').select2();
