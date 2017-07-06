<link rel="stylesheet" type="text/css" media="all" href="{$css nofilter}">
<div class="paylater-content">
    <!-- Title and spinner -->
    <h3>{l s='Comenzando tu financiación' mod='paylater'}</h3>
    <img src="{$spinner nofilter}">
    <!-- Iframe Div -->
    <div id="myModal" class="paylater_modal" style="display: none;">
        <div class="paylater_modal-content">
            <iframe id="iframe-pagantis" name="iframe-pagantis" style="width: 100%; height: 100%; display: block" frameborder="0"></iframe>
            <button class="paylater_modal-close" id="paylater_close" title="Cerrar" type="button">X</button>
        </div>
    </div>
    <!-- open payment button -->
    <div class="col-xs-12">
        <p class="payment_module paylater_payment_button" id="paylater_payment_button">
            <a href="javascript:$('#paylater_form').submit();" title="Pay later" class="paylater_payment_link">
                Abrir Paga+Tarde
            </a>
        </p>
        <div class="PmtSimulator PmtSimulatorSelectable--claim" data-pmt-num-quota="4" data-pmt-style="grey" data-pmt-type="4" data-pmt-discount="0" data-pmt-amount="19.21" id="0"></div>
    </div>
    <!-- Payment Form Render -->
    {$form nofilter}
    <!-- functionality for iframe or redirect -->
    {if $iframe == true }
        <script type="text/javascript">
            el = document.getElementById("paylater_payment_button");
            el.addEventListener('click', function (e){
                e.preventDefault();
                document.getElementById('paylater_form').setAttribute('target', 'iframe-pagantis');
                document.getElementById('paylater_form').submit();
                document.getElementById('iframe-pagantis').style.display = 'block';
                document.getElementById('myModal').style.display = 'block';
            });
            var closeModal = function closeModal(evt) {
                evt.preventDefault();
                document.getElementById('myModal').style.display = 'none';
            };

            var elements = document.querySelectorAll('#paylater_close, #myModal');
            Array.prototype.forEach.call(elements, function(el){
                el.addEventListener('click', closeModal);
            });

            el.click();
        </script>
    {else}
        <script type="text/javascript">
            document.getElementById('paylater_form').submit();
        </script>
    {/if}
</div>
