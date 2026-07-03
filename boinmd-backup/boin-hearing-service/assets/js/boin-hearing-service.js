(function(){
  var activeAudio = null;

  function qs(sel, root){ return (root || document).querySelector(sel); }
  function qsa(sel, root){ return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  function msg(root, text, ok){
    var el = qs('.bhs-form-msg', root) || qs('.bhs-form-msg');
    if(!el) return;
    el.textContent = text || '';
    el.classList.toggle('is-ok', !!ok);
    el.classList.toggle('is-error', !ok && !!text);
  }

  function logAudioError(stage, err, audio, url){
    if(!window.console || !console.error) return;
    var mediaError = audio && audio.error ? {
      code: audio.error.code,
      message: audio.error.message || ''
    } : null;
    console.error('[BHS audio]', stage, {
      url: url,
      errorName: err && err.name,
      errorMessage: err && err.message,
      mediaError: mediaError,
      networkState: audio && audio.networkState,
      readyState: audio && audio.readyState,
      currentSrc: audio && audio.currentSrc
    });
  }

  async function post(endpoint, payload){
    var res = await fetch(BHS_DATA.restUrl + endpoint, {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-WP-Nonce':BHS_DATA.nonce},
      body: JSON.stringify(payload || {})
    });
    var data = await res.json().catch(function(){ return {}; });
    if(!res.ok || data.success === false){
      throw new Error(data.message || (data.data && data.data.message) || '操作失败，请稍后再试。');
    }
    return data.data || data;
  }

  function withTimeout(promise, ms, onTimeout){
    var timer = null;
    var timeout = new Promise(function(_, reject){
      timer = setTimeout(function(){
        if(typeof onTimeout === 'function') onTimeout();
        reject(new Error('声音播放超时，请检查浏览器音量或稍后重试。'));
      }, ms);
    });
    return Promise.race([promise, timeout]).finally(function(){
      if(timer) clearTimeout(timer);
    });
  }

  function audioUrl(ear, freq, level){
    var map = BHS_DATA.audioMap || {};
    if(ear === 'reference'){
      return map.reference && map.reference.left && map.reference.left['3'];
    }
    var byEar = map[ear] || {};
    var byFreq = byEar[String(freq)] || {};
    return byFreq[String(level)] || '';
  }

  function stopActiveAudio(){
    if(!activeAudio) return;
    try{
      activeAudio.pause();
      activeAudio.removeAttribute('src');
      while(activeAudio.firstChild) activeAudio.removeChild(activeAudio.firstChild);
      activeAudio.load();
    }catch(e){}
    activeAudio = null;
  }

  function makeAudio(url){
    var audio = new Audio();
    audio.src = url;
    audio.preload = 'auto';
    audio.playsInline = true;
    audio.volume = 1;
    audio.muted = false;
    return audio;
  }

  async function playAudioFile(url){
    if(!url) throw new Error('声音文件未找到，请稍后重试。');
    stopActiveAudio();

    var audio = makeAudio(url);
    activeAudio = audio;

    await withTimeout(new Promise(function(resolve, reject){
      var settled = false;
      function cleanup(){
        audio.removeEventListener('ended', onEnded);
        audio.removeEventListener('error', onError);
        audio.removeEventListener('stalled', onStalled);
        audio.removeEventListener('abort', onAbort);
      }
      function finish(err, stage){
        if(settled) return;
        settled = true;
        cleanup();
        if(activeAudio === audio) activeAudio = null;
        if(err){
          logAudioError(stage || 'finish', err, audio, url);
          reject(err);
        }else{
          resolve();
        }
      }
      function onEnded(){ finish(); }
      function onError(){ finish(new Error('声音播放失败，请检查浏览器音量或稍后重试。'), 'media-error'); }
      function onStalled(){ logAudioError('stalled', new Error('audio stalled'), audio, url); }
      function onAbort(){ logAudioError('abort', new Error('audio aborted'), audio, url); }

      audio.addEventListener('ended', onEnded);
      audio.addEventListener('error', onError);
      audio.addEventListener('stalled', onStalled);
      audio.addEventListener('abort', onAbort);
      audio.load();

      var playPromise;
      try{
        playPromise = audio.play();
      }catch(err){
        finish(err, 'play-throw');
        return;
      }

      if(playPromise && typeof playPromise.catch === 'function'){
        playPromise.catch(function(err){
          finish(err || new Error('声音播放失败，请检查浏览器音量或稍后重试。'), 'play-reject');
        });
      }
    }), 25000, function(){ stopActiveAudio(); });
  }

  function setButtonsDisabled(root, selector, disabled){
    qsa(selector, root).forEach(function(btn){ btn.disabled = disabled; });
  }

  function revealCalibrationOptions(card){
    var options = qs('.bhs-calibration-options', card);
    if(options) options.hidden = false;
  }


  function phoneDigits(value){
    return String(value || '').replace(/\D+/g, '');
  }

  function isPhoneReady(card){
    var input = qs('[data-bhs-phone]', card);
    return !input || phoneDigits(input.value).length === 11;
  }

  function updateReferenceButton(card){
    if(!card) return;
    var btn = qs('[data-bhs-play-reference]', card);
    if(!btn) return;
    var ready = isPhoneReady(card);
    btn.disabled = !ready;
    btn.classList.toggle('is-disabled', !ready);
  }

  document.addEventListener('visibilitychange', function(){
    if(document.hidden) stopActiveAudio();
  });

  window.addEventListener('pagehide', stopActiveAudio);
  function scrollToTestCard(){
    var testCard = qs('[data-bhs-page="test"]');
    if(!testCard) return;
    setTimeout(function(){
      try{
        testCard.scrollIntoView({behavior: 'smooth', block: 'start'});
      }catch(e){
        window.scrollTo(0, Math.max(0, testCard.getBoundingClientRect().top + window.pageYOffset - 16));
      }
    }, 120);
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', scrollToTestCard);
  }else{
    scrollToTestCard();
  }

  document.addEventListener('change', function(e){
    if(e.target.matches('[data-bhs-intro-consent]')){
      var next = qs('[data-bhs-intro-next]');
      if(next) next.classList.toggle('is-disabled', !e.target.checked);
    }
  });


  document.addEventListener('input', function(e){
    if(e.target.matches('[data-bhs-phone]')){
      var card = e.target.closest('[data-bhs-page]');
      updateReferenceButton(card);
    }
  });

  function initCalibrationCards(){
    qsa('[data-bhs-page="calibration"]').forEach(updateReferenceButton);
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', initCalibrationCards);
  }else{
    initCalibrationCards();
  }

  document.addEventListener('click', async function(e){
    var introNext = e.target.closest('[data-bhs-intro-next]');
    if(introNext && introNext.classList.contains('is-disabled')){
      e.preventDefault();
      return;
    }

    var playRef = e.target.closest('[data-bhs-play-reference]');
    if(playRef){
      var card = playRef.closest('[data-bhs-page]');
      var revealTimer = null;
      try{
        playRef.disabled = true;
        playRef.classList.add('is-disabled');
        msg(card, '正在播放参考声音，请留意当前音量是否清楚舒适。', true);
        revealTimer = setTimeout(function(){
          revealCalibrationOptions(card);
          msg(card, '参考声音正在播放。如果已经能听清，请选择当前音量感受。', true);
        }, 1000);
        await playAudioFile(audioUrl('reference'));
        revealCalibrationOptions(card);
        msg(card, '播放完成，请选择当前音量感受。', true);
      }catch(err){
        revealCalibrationOptions(card);
        msg(card, err.message + ' 如果您已确认设备音量正常，也可以选择“音量合适”继续。', false);
      }finally{
        if(revealTimer) clearTimeout(revealTimer);
        updateReferenceButton(card);
      }
    }

    var volume = e.target.closest('[data-bhs-volume]');
    if(volume){
      var card2 = volume.closest('[data-bhs-page]');
      var val = volume.getAttribute('data-bhs-volume');
      var start = qs('[data-bhs-start-test]', card2);
      if(val === 'ok'){
        start.disabled = false;
        start.classList.remove('is-disabled');
        msg(card2, '音量已确认，可以开始左耳测试。', true);
      }
      if(val === 'small'){
        msg(card2, '请适当提高设备媒体音量，然后重新播放参考声音。', false);
      }
      if(val === 'large'){
        msg(card2, '请降低设备媒体音量，然后重新播放参考声音。', false);
      }
    }

    var startTest = e.target.closest('[data-bhs-start-test]');
    if(startTest){
      var card3 = startTest.closest('[data-bhs-page]');
      try{
        startTest.disabled = true;
        msg(card3, '正在创建测试会话...', true);
        var created = await post('sessions', {});
        await post('sessions/' + created.session_uuid + '/calibration', {
          token: created.session_token,
          headphone_confirmed: true,
          volume_confirmed: true
        });
        window.location.href = created.next_url.replace('/test-calibration/', '/test/');
      }catch(err){
        startTest.disabled = false;
        msg(card3, err.message, false);
      }
    }

    var play = e.target.closest('[data-bhs-play-tone], [data-bhs-replay-tone]');
    if(play){
      var test = play.closest('[data-bhs-page="test"]');
      var unlockTimer = null;
      try{
        setButtonsDisabled(test, '[data-bhs-answer]', true);
        play.disabled = true;
        msg(test, '正在播放声音，听到后即可作答。', true);
        unlockTimer = setTimeout(function(){
          setButtonsDisabled(test, '[data-bhs-answer]', false);
          var replayEarly = qs('[data-bhs-replay-tone]', test);
          if(replayEarly) replayEarly.disabled = false;
        }, 1000);
        await playAudioFile(audioUrl(test.dataset.ear, test.dataset.frequency, test.dataset.level));
        msg(test, '播放完成。如果刚才听到了，请选择“听到了”；如果没听到，请选择“没听到”。', true);
        setButtonsDisabled(test, '[data-bhs-answer]', false);
        var replay = qs('[data-bhs-replay-tone]', test);
        if(replay) replay.disabled = false;
      }catch(err){
        msg(test, err.message, false);
        setButtonsDisabled(test, '[data-bhs-answer]', false);
        var retry = qs('[data-bhs-replay-tone]', test) || qs('[data-bhs-play-tone]', test);
        if(retry) retry.disabled = false;
      }finally{
        if(unlockTimer) clearTimeout(unlockTimer);
        play.disabled = false;
      }
    }

    var answer = e.target.closest('[data-bhs-answer]');
    if(answer){
      var test2 = answer.closest('[data-bhs-page="test"]');
      try{
        setButtonsDisabled(test2, '[data-bhs-answer], [data-bhs-play-tone], [data-bhs-replay-tone]', true);
        msg(test2, '正在保存当前结果...', true);
        var saved = await post('sessions/' + test2.dataset.session + '/answer', {
          token: test2.dataset.token,
          answer: answer.dataset.bhsAnswer
        });
        window.location.href = saved.next_url;
      }catch(err){
        msg(test2, err.message, false);
        setButtonsDisabled(test2, '[data-bhs-answer], [data-bhs-play-tone], [data-bhs-replay-tone]', false);
      }
    }

    var stop = e.target.closest('[data-bhs-stop-test]');
    if(stop){
      var test3 = stop.closest('[data-bhs-page="test"]');
      var reason = (qs('[data-bhs-stop-reason]', test3) || {}).value || '暂时不想继续';
      try{
        var stopped = await post('sessions/' + test3.dataset.session + '/interrupt', {
          token: test3.dataset.token,
          reason: reason
        });
        window.location.href = stopped.next_url;
      }catch(err){
        msg(test3, err.message, false);
      }
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
      setButtonsDisabled(form, 'button', true);
      msg(form, '正在提交需求...', true);
      var saved = await post('requests', payload);
      window.location.href = saved.next_url;
    }catch(err){
      setButtonsDisabled(form, 'button', false);
      msg(form, err.message, false);
    }
  });
})();