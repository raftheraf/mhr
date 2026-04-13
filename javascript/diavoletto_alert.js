(function () {
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof miei_diavoletti === 'undefined' || miei_diavoletti.length === 0) {
            return;
        }
        mostraListaDiavoletti(miei_diavoletti);
    });

    function creaOverlay() {
        var overlay = document.createElement('div');
        overlay.id = 'diavoletto-overlay';
        overlay.style.cssText = [
            'position:fixed',
            'top:0',
            'left:0',
            'width:100%',
            'height:100%',
            'background:rgba(0,0,0,0.85)',
            'z-index:99999',
            'overflow-y:auto'
        ].join(';');
        return overlay;
    }

    function creaBox() {
        var box = document.createElement('div');
        box.id = 'diavoletto-box';
        box.style.cssText = [
            'background:#8B0000',
            'color:white',
            'border:3px solid #FF6600',
            'border-radius:10px',
            'padding:30px',
            'max-width:420px',
            'width:90%',
            'text-align:center',
            'box-sizing:border-box',
            'margin:10% auto 0 auto'
        ].join(';');
        return box;
    }

    function mostraListaDiavoletti(lista) {
        var overlay = creaOverlay();
        var box = creaBox();

        var titolo = document.createElement('h2');
        titolo.style.cssText = 'color:white;font-size:28px;margin:0 0 10px 0;';
        titolo.innerHTML = '&#128520; ATTENZIONE!';

        var sottotitolo = document.createElement('p');
        sottotitolo.style.cssText = 'color:white;font-size:16px;margin:0 0 20px 0;';
        sottotitolo.textContent = 'I seguenti tavoli sono fermi da troppo tempo:';

        box.appendChild(titolo);
        box.appendChild(sottotitolo);

        for (var i = 0; i < lista.length; i++) {
            (function (item) {
                var btn = document.createElement('button');
                btn.style.cssText = [
                    'display:block',
                    'width:100%',
                    'padding:15px',
                    'margin-bottom:10px',
                    'background:#FF6600',
                    'color:white',
                    'border:none',
                    'border-radius:5px',
                    'font-size:20px',
                    'cursor:pointer',
                    'min-height:50px'
                ].join(';');
                btn.innerHTML = '&#128520; ' + item.nome;
                btn.onclick = function () {
                    mostraConferma(item, lista);
                };
                box.appendChild(btn);
            })(lista[i]);
        }

        overlay.appendChild(box);
        document.body.appendChild(overlay);
    }

    function mostraConferma(item, lista) {
        var box = document.getElementById('diavoletto-box');
        box.innerHTML = '';

        var titolo = document.createElement('h2');
        titolo.style.cssText = 'color:white;font-size:28px;margin:0 0 10px 0;';
        titolo.innerHTML = '&#128520; ' + item.nome;

        var domanda = document.createElement('p');
        domanda.style.cssText = 'color:white;font-size:18px;margin:0 0 30px 0;';
        domanda.textContent = 'Vuoi aprire questo tavolo?';

        var btnRow = document.createElement('div');
        btnRow.style.cssText = 'width:100%;';

        var btnIndietro = document.createElement('button');
        btnIndietro.style.cssText = [
            'width:48%',
            'margin-right:4%',
            'padding:15px',
            'background:#555555',
            'color:white',
            'border:none',
            'border-radius:5px',
            'font-size:18px',
            'cursor:pointer',
            'min-height:50px'
        ].join(';');
        btnIndietro.textContent = 'INDIETRO';
        btnIndietro.onclick = function () {
            var overlay = document.getElementById('diavoletto-overlay');
            overlay.parentNode.removeChild(overlay);
            mostraListaDiavoletti(lista);
        };

        var btnApri = document.createElement('button');
        btnApri.style.cssText = [
            'width:48%',
            'padding:15px',
            'background:#FF6600',
            'color:white',
            'border:none',
            'border-radius:5px',
            'font-size:18px',
            'cursor:pointer',
            'min-height:50px'
        ].join(';');
        btnApri.textContent = 'APRI';
        btnApri.onclick = function () {
            window.location.href = item.link;
        };

        btnRow.appendChild(btnIndietro);
        btnRow.appendChild(btnApri);

        box.appendChild(titolo);
        box.appendChild(domanda);
        box.appendChild(btnRow);
    }
})();
