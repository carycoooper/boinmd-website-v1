(function(){
  function qs(sel, root){ return (root || document).querySelector(sel); }
  function qsa(sel, root){ return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
  function msg(root, text, ok){
    var el = qs('.bhs-form-msg', root) || qs('.bhs-form-msg');
    if(!el) return;
    el.textContent = text || '';
    el.classList.toggle('is-ok', !!ok);
    el.classList.toggle('is-error', !ok && !!text);
  }
  async function post(endpoint, payload){
    var res = await fetch(BHS_DATA.restUrl + endpoint, {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-WP-Nonce':BHS_DATA.nonce},
      body: JSON.stringify(payload || {})
    });
    var data = await res.json().catch(function(){ return {}; });
    if(!res.ok || data.success === false){ throw new Error(data.message || (data.data && data.data.message) || '操作失败，请稍后再试。'); }
    return data.data || data;
  }
  function safeAudioContext(){
    var Ctx = window.AudioContext || window.webkitAudioContext;
    if(!Ctx) throw new Error('当前浏览器暂不支持音频测试，请更换最新版 Chrome、Edge 或 Safari 后重试。');
    return new Ctx();
  }
  async function playTone(freq, level, ear){
    var ctx = safeAudioContext();
    if(ctx.state === 'suspended') await ctx.resume();
    if(typeof ctx.createChannelMerger !== 'function') throw new Error('当前浏览器无法稳定控制左右声道，请更换浏览器后重试。');

    return new Promise(function(resolve, reject){
      var done = false;
      var timeout = null;
      var osc = null;
      function finish(err){
        if(done) return;
        done = true;
        if(timeout) clearTimeout(timeout);
        try{ if(osc) osc.disconnect(); }catch(e){}
        try{ ctx.close(); }catch(e){}
        if(err) reject(err); else resolve();
      }

      try{
        osc = ctx.createOscillator();
        var gain = ctx.createGain();
        var merger = ctx.createChannelMerger(2);
        var duration = 1.2;
        var now = ctx.currentTime;
        var safeGain = Math.min(0.16, 0.035 + (Number(level || 3) * 0.018));
        osc.type = 'sine';
        osc.frequency.value = Number(freq || 1000);
        gain.gain.setValueAtTime(0.0001, now);
        gain.gain.exponentialRampToValueAtTime(safeGain, now + 0.08);
        gain.gain.setValueAtTime(safeGain, now + duration - 0.12);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + duration);
        osc.connect(gain);
        gain.connect(merger, 0, ear === 'right' ? 1 : 0);
        merger.connect(ctx.destination);
        osc.onended = function(){ finish(); };
        timeout = setTimeout(function(){ finish(new Error('声音播放超时，请检查浏览器音频权限或更换浏览器后重试。')); }, 4000);
        osc.start(now);
        osc.stop(now + duration);
      }catch(err){
        finish(err);
      }
    });
  }

  document.addEventListener('change', function(e){
    if(e.target.matches('[data-bhs-intro-consent]')){
      var next = qs('[data-bhs-intro-next]');
      if(next) next.classList.toggle('is-disabled', !e.target.checked);
    }
  });
  document.addEventListener('click', async function(e){
    var introNext = e.target.closest('[data-bhs-intro-next]');
    if(introNext && introNext.classList.contains('is-disabled')){ e.preventDefault(); return; }

    var playRef = e.target.closest('[data-bhs-play-reference]');
    if(playRef){
      var card = playRef.closest('[data-bhs-page]');
      try{ playRef.disabled = true; msg(card,'正在播放参考声音...',true); await playTone(1000,3,'left'); qs('.bhs-calibration-options', card).hidden = false; msg(card,'请选择当前音量感受。',true); }
      catch(err){ msg(card, err.message, false); }
      finally{ playRef.disabled = false; }
    }

    var volume = e.target.closest('[data-bhs-volume]');
    if(volume){
      var card2 = volume.closest('[data-bhs-page]');
      var val = volume.getAttribute('data-bhs-volume');
      var start = qs('[data-bhs-start-test]', card2);
      if(val === 'ok') { start.disabled = false; start.classList.remove('is-disabled'); msg(card2,'音量已确认，可以开始左耳测试。',true); }
      if(val === 'small') msg(card2,'请适当提高设备媒体音量，然后重新播放参考声音。',false);
      if(val === 'large') msg(card2,'请降低设备媒体音量，然后重新播放参考声音。',false);
    }

    var startTest = e.target.closest('[data-bhs-start-test]');
    if(startTest){
      var card3 = startTest.closest('[data-bhs-page]');
      var phone = (qs('[data-bhs-phone]', card3) || {}).value || '';
      if(!phone){ msg(card3,'请先填写手机号，便于验配师查看本次筛查记录。',false); return; }
      try{
        startTest.disabled = true; msg(card3,'正在创建测试会话...',true);
        var created = await post('sessions', {phone: phone});
        await post('sessions/' + created.session_uuid + '/calibration', {token: created.session_token, headphone_confirmed: true, volume_confirmed: true});
        window.location.href = created.next_url.replace('/test-calibration/','/test/');
      }catch(err){ startTest.disabled = false; msg(card3,err.message,false); }
    }

    var play = e.target.closest('[data-bhs-play-tone], [data-bhs-replay-tone]');
    if(play){
      var test = play.closest('[data-bhs-page="test"]');
      try{
        qsa('[data-bhs-answer]', test).forEach(function(b){ b.disabled = true; });
        play.disabled = true; msg(test,'正在播放声音...',true);
        await playTone(test.dataset.frequency, test.dataset.level, test.dataset.ear);
        msg(test,'播放完成，请选择是否听到。',true);
        qsa('[data-bhs-answer]', test).forEach(function(b){ b.disabled = false; });
        var replay = qs('[data-bhs-replay-tone]', test); if(replay) replay.disabled = false;
      }catch(err){ msg(test,err.message,false); }
      finally{ play.disabled = false; }
    }

    var answer = e.target.closest('[data-bhs-answer]');
    if(answer){
      var test2 = answer.closest('[data-bhs-page="test"]');
      try{
        qsa('[data-bhs-answer], [data-bhs-play-tone], [data-bhs-replay-tone]', test2).forEach(function(b){ b.disabled = true; });
        msg(test2,'正在保存当前结果...',true);
        var saved = await post('sessions/' + test2.dataset.session + '/answer', {token:test2.dataset.token, answer:answer.dataset.bhsAnswer});
        window.location.href = saved.next_url;
      }catch(err){ msg(test2,err.message,false); qsa('[data-bhs-answer], [data-bhs-play-tone]', test2).forEach(function(b){ b.disabled = false; }); }
    }

    var stop = e.target.closest('[data-bhs-stop-test]');
    if(stop){
      var test3 = stop.closest('[data-bhs-page="test"]');
      var reason = (qs('[data-bhs-stop-reason]', test3) || {}).value || '暂时不想继续';
      try{ var stopped = await post('sessions/' + test3.dataset.session + '/interrupt', {token:test3.dataset.token, reason:reason}); window.location.href = stopped.next_url; }
      catch(err){ msg(test3,err.message,false); }
    }
  });

  document.addEventListener('submit', async function(e){
    var form = e.target.closest('[data-bhs-request-form]');
    if(!form) return;
    e.preventDefault();
    var fd = new FormData(form);
    var payload = {feedback_options: []};
    fd.forEach(function(value, key){
      if(key === 'feedback_options[]') payload.feedback_options.push(value);
      else payload[key] = value;
    });
    payload.privacy_confirmed = !!fd.get('privacy_confirmed');
    try{
      qsa('button', form).forEach(function(b){ b.disabled = true; });
      msg(form,'正在提交需求...',true);
      var saved = await post('requests', payload);
      window.location.href = saved.next_url;
    }catch(err){ qsa('button', form).forEach(function(b){ b.disabled = false; }); msg(form,err.message,false); }
  });
})();
