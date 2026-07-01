(function(){
  function setMessage(form, text, ok){
    var msg = form.querySelector('.bhs-form-msg');
    if(!msg) return;
    msg.textContent = text || '';
    msg.classList.toggle('is-ok', !!ok);
    msg.classList.toggle('is-error', !ok && !!text);
  }

  async function postJSON(endpoint, payload){
    var res = await fetch(BHS_DATA.restUrl + endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': BHS_DATA.nonce
      },
      body: JSON.stringify(payload)
    });
    var data = await res.json().catch(function(){ return {}; });
    if(!res.ok || data.success === false){
      throw new Error(data.message || (data.data && data.data.message) || '提交失败，请稍后再试。');
    }
    return data;
  }

  document.addEventListener('submit', async function(event){
    var form = event.target.closest('[data-bhs-form]');
    if(!form) return;
    event.preventDefault();
    setMessage(form, '正在提交...', true);

    var type = form.getAttribute('data-bhs-form');
    var fd = new FormData(form);
    var payload = {};

    if(type === 'request'){
      payload.user_phone = fd.get('user_phone') || '';
      payload.device_model = fd.get('device_model') || '';
      payload.description = fd.get('description') || '';
      payload.test_id = fd.get('test_id') || '';
    }

    if(type === 'test'){
      payload.user_phone = fd.get('user_phone') || '';
      payload.summary = fd.get('summary') || '';
      payload.freq_result = {};
      ['250','500','1000','2000','4000','8000'].forEach(function(freq){
        payload.freq_result[freq] = fd.get('freq_' + freq) || '';
      });
    }

    try{
      var data = await postJSON(type, payload);
      setMessage(form, '提交成功，我们已收到。记录 ID：' + data.id, true);
      form.reset();
      if(type === 'request'){
        window.location.href = '/hearing-service/?bhs_step=success';
      }
    }catch(err){
      setMessage(form, err.message, false);
    }
  });
})();

