<input type="hidden" id="pago_reserva_reembolso_url" value="{{ url('medico/reembolsar_reserva_turno') }}">

<div class="modal fade" id="modalDetallePagoReserva" tabindex="-1" role="dialog" aria-labelledby="modalDetallePagoReservaLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="modalDetallePagoReservaLabel">Pago de reserva online</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="mb-1"><strong>Paciente:</strong> <span id="pago_reserva_paciente"></span></p>
        <p class="mb-1"><strong>Horario:</strong> <span id="pago_reserva_horario"></span></p>
        <hr>
        <p class="mb-1"><strong>Importe:</strong> $<span id="pago_reserva_importe"></span></p>
        <p class="mb-1"><strong>Estado:</strong> <span id="pago_reserva_estado"></span></p>
        <p class="mb-1"><strong>ID pago Mercado Pago:</strong></p>
        <div class="input-group input-group-sm mb-2">
          <input type="text" class="form-control" id="pago_reserva_payment_id" readonly>
          <div class="input-group-append">
            <button type="button" class="btn btn-outline-secondary" onclick="copiarTextoPagoReserva('pago_reserva_payment_id')">Copiar</button>
          </div>
        </div>
        <p class="mb-1 small text-muted" id="pago_reserva_preference_wrap" style="display:none;">
          <strong>ID preferencia:</strong>
          <span id="pago_reserva_preference_id"></span>
        </p>
        <div id="pago_reserva_reembolso_wrap" style="display:none;">
          <hr>
          <div class="checkbox">
            <label>
              <input type="checkbox" id="pago_reserva_cancelar_turno"> También cancelar el turno
            </label>
          </div>
        </div>
        <div id="pago_reserva_mensaje" class="mt-2" style="display:none;"></div>
        <div id="pago_reserva_spinner" class="text-center mt-2" style="display:none;">
          <span class="glyphicon glyphicon-refresh glyphicon-spin"></span> Procesando reembolso...
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-warning" id="pago_reserva_btn_reembolsar" style="display:none;" onclick="ejecutarReembolsoReserva()">Reembolsar reserva</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
window._pagosReservaDetalle = window._pagosReservaDetalle || {};
window._pagoReservaTridActual = null;

function moduloCobroTurnosMpActivo() {
  var el = document.getElementById('modulo_cobro_turnos_mp');
  return el && el.value === '1';
}

function puedeReembolsarReserva() {
  var el = document.getElementById('secretaria_puede_reembolso');
  return !el || el.value === '1';
}

function formatearImportePagoReserva(valor) {
  var n = parseFloat(valor);
  if (isNaN(n)) return '0,00';
  return n.toFixed(2).replace('.', ',');
}

function etiquetaEstadoPagoReserva(estado) {
  if (estado === 'approved') return 'Pagado';
  if (estado === 'refunded') return 'Reembolsado';
  if (!estado || estado === '—') return '—';
  return estado;
}

function celdaPagoReservaHtml(row) {
  if (!moduloCobroTurnosMpActivo()) {
    return '';
  }
  if (parseInt(row.pago, 10) !== 1) {
    return "<td class='editText text-center text-muted'>—</td>";
  }
  window._pagosReservaDetalle[row.trid] = {
    trid: row.trid,
    paciente: (row.apellidop || '') + ', ' + (row.nombrep || ''),
    horario: row.horario || '',
    importe: row.importe_reserva,
    estado: row.pago_estado || '',
    paymentId: row.mercadopago_payment_id || '',
    preferenceId: row.mercadopago_preference_id || ''
  };
  var celdaId = 'pago_reserva_celda_' + row.trid;
  var contenido = '';
  if (row.pago_estado === 'refunded') {
    contenido = "<span class='text-muted' title='Reserva reembolsada'>Reemb.</span>";
  } else {
    contenido = "<button type='button' class='btn btn-link p-0 border-0' title='Ver pago de reserva' onclick='mostrarDetallePagoReserva(" + row.trid + ")'><span style='color:#28a745;font-size:1.35rem;line-height:1;'>&#10003;</span></button>";
  }
  return "<td class='editText text-center' id='" + celdaId + "'>" + contenido + "</td>";
}

function insertarColumnaPagoEnFila(filaHtml, row) {
  if (!moduloCobroTurnosMpActivo()) {
    return filaHtml;
  }
  var celda = celdaPagoReservaHtml(row);
  if (filaHtml.indexOf("rodri_button_aceptar_si") >= 0 || filaHtml.indexOf("rodri_button_cancelar_no") >= 0) {
    return filaHtml.replace(/<td><button class='rodri_button_(aceptar_si|cancelar_no)/, celda + "<td><button class='rodri_button_$1");
  }
  return filaHtml.replace(/(<td[^>]*><\/td>\s*)<\/tr>\s*$/, celda + '$1</tr>');
}

function actualizarCeldaPagoReserva(trid) {
  var d = window._pagosReservaDetalle[trid];
  var celda = document.getElementById('pago_reserva_celda_' + trid);
  if (!d || !celda) return;
  if (d.estado === 'refunded') {
    celda.innerHTML = "<span class='text-muted' title='Reserva reembolsada'>Reemb.</span>";
  }
}

function mostrarMensajePagoReserva(texto, esError) {
  var el = document.getElementById('pago_reserva_mensaje');
  if (!el) return;
  el.style.display = 'block';
  el.className = 'mt-2 alert alert-' + (esError ? 'danger' : 'success');
  el.textContent = texto;
}

function limpiarMensajePagoReserva() {
  var el = document.getElementById('pago_reserva_mensaje');
  if (el) {
    el.style.display = 'none';
    el.textContent = '';
  }
}

function actualizarBotonReembolso(estado) {
  var btn = document.getElementById('pago_reserva_btn_reembolsar');
  var wrap = document.getElementById('pago_reserva_reembolso_wrap');
  var puedeReembolsar = estado === 'approved' && puedeReembolsarReserva();
  if (btn) btn.style.display = puedeReembolsar ? 'inline-block' : 'none';
  if (wrap) wrap.style.display = puedeReembolsar ? 'block' : 'none';
}

function mostrarDetallePagoReserva(trid) {
  var d = window._pagosReservaDetalle[trid];
  if (!d) return;
  window._pagoReservaTridActual = trid;
  limpiarMensajePagoReserva();
  document.getElementById('pago_reserva_spinner').style.display = 'none';
  document.getElementById('pago_reserva_cancelar_turno').checked = false;
  document.getElementById('pago_reserva_paciente').textContent = d.paciente;
  document.getElementById('pago_reserva_horario').textContent = d.horario;
  document.getElementById('pago_reserva_importe').textContent = formatearImportePagoReserva(d.importe);
  document.getElementById('pago_reserva_estado').textContent = etiquetaEstadoPagoReserva(d.estado);
  document.getElementById('pago_reserva_payment_id').value = d.paymentId || '—';
  var prefWrap = document.getElementById('pago_reserva_preference_wrap');
  if (d.preferenceId) {
    prefWrap.style.display = 'block';
    document.getElementById('pago_reserva_preference_id').textContent = d.preferenceId;
  } else {
    prefWrap.style.display = 'none';
  }
  actualizarBotonReembolso(d.estado);
  $('#modalDetallePagoReserva').modal('show');
}

function ejecutarReembolsoReserva() {
  var trid = window._pagoReservaTridActual;
  var d = window._pagosReservaDetalle[trid];
  if (!d || d.estado !== 'approved' || !puedeReembolsarReserva()) return;

  var cancelarTurno = document.getElementById('pago_reserva_cancelar_turno').checked ? 1 : 0;
  var msg = '¿Confirma el reembolso de $' + formatearImportePagoReserva(d.importe) + '?';
  if (cancelarTurno) {
    msg += ' El turno también será cancelado.';
  }
  if (!confirm(msg)) return;

  var url = document.getElementById('pago_reserva_reembolso_url').value;
  var csrf = '';
  var meta = document.querySelector('meta[name="csrf-token"]');
  if (meta) csrf = meta.getAttribute('content');

  document.getElementById('pago_reserva_btn_reembolsar').disabled = true;
  document.getElementById('pago_reserva_spinner').style.display = 'block';
  limpiarMensajePagoReserva();

  $.ajax({
    type: 'POST',
    dataType: 'JSON',
    url: url,
    data: {
      turno_id: trid,
      cancelar_turno: cancelarTurno,
      _token: csrf
    },
    success: function(res) {
      document.getElementById('pago_reserva_spinner').style.display = 'none';
      document.getElementById('pago_reserva_btn_reembolsar').disabled = false;
      if (res.success) {
        d.estado = res.pago_estado || 'refunded';
        document.getElementById('pago_reserva_estado').textContent = etiquetaEstadoPagoReserva(d.estado);
        actualizarBotonReembolso(d.estado);
        actualizarCeldaPagoReserva(trid);
        mostrarMensajePagoReserva(res.message || 'Reembolso realizado correctamente.', false);
      } else {
        mostrarMensajePagoReserva(res.message || 'No se pudo realizar el reembolso.', true);
      }
    },
    error: function(xhr) {
      document.getElementById('pago_reserva_spinner').style.display = 'none';
      document.getElementById('pago_reserva_btn_reembolsar').disabled = false;
      var msg = 'No se pudo realizar el reembolso.';
      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      }
      mostrarMensajePagoReserva(msg, true);
    }
  });
}

function copiarTextoPagoReserva(inputId) {
  var input = document.getElementById(inputId);
  if (!input || !input.value || input.value === '—') return;
  input.select();
  input.setSelectionRange(0, 99999);
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(input.value);
  } else {
    document.execCommand('copy');
  }
}
</script>
