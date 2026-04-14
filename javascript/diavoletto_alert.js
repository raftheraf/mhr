(function () {
    var _alertInterval = null;
    var _audioCtx = null;

    function avviaBeep() {
        if (_alertInterval) return;
        try {
            _audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) { return; }
        _alertInterval = setInterval(function () {
            var osc = _audioCtx.createOscillator();
            osc.connect(_audioCtx.destination);
            osc.frequency.value = 880;
            osc.start();
            osc.stop(_audioCtx.currentTime + 0.3);
        }, 1000);
    }

    function fermaBeep() {
        if (_alertInterval) {
            clearInterval(_alertInterval);
            _alertInterval = null;
        }
        if (_audioCtx) {
            try { _audioCtx.close(); } catch (e) {}
            _audioCtx = null;
        }
    }

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
        avviaBeep();
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
                    fermaBeep();
                    window.location.href = item.link;
                };
                box.appendChild(btn);
            })(lista[i]);
        }

        var btnIgnora = document.createElement('button');
        btnIgnora.style.cssText = [
            'display:block',
            'width:100%',
            'padding:12px',
            'margin-top:15px',
            'background:#555555',
            'color:white',
            'border:none',
            'border-radius:5px',
            'font-size:16px',
            'cursor:pointer',
            'min-height:44px'
        ].join(';');
        btnIgnora.textContent = 'Ignora Avviso';
        btnIgnora.onclick = function () {
            fermaBeep();
            var ov = document.getElementById('diavoletto-overlay');
            ov.parentNode.removeChild(ov);
        };
        box.appendChild(btnIgnora);

        overlay.appendChild(box);
        document.body.appendChild(overlay);
    }


})();
