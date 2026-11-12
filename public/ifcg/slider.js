(() => {
 document.querySelectorAll('[data-slider]').forEach(root => {
  const slides=[...root.querySelectorAll('[data-slide]')];
  if(slides.length<2)return;
  const motion=matchMedia('(prefers-reduced-motion: reduce)');
  const control=root.querySelector('[data-autoplay]');
  let current=0,timer=null,paused=motion.matches,hovered=false;
  const playing=()=>[...slides[current].querySelectorAll('video')].some(v=>!v.paused&&!v.ended);
  function stop(){clearTimeout(timer);timer=null;}
  function schedule(){stop();if(!paused&&!hovered&&!document.hidden&&!root.contains(document.activeElement)&&!playing())timer=setTimeout(()=>{show(current+1);schedule();},1500);}
  function show(index){
   slides[current].querySelectorAll('video').forEach(video=>video.pause());
   slides[current].hidden=true;current=(index+slides.length)%slides.length;slides[current].hidden=false;
   const counter=root.querySelector('[data-counter]');if(counter)counter.textContent=`${current+1} / ${slides.length}`;
  }
  function syncControl(){if(control){control.textContent=paused?'▶':'Ⅱ';control.setAttribute('aria-label',paused?control.dataset.playLabel:control.dataset.pauseLabel);control.setAttribute('aria-pressed',String(paused));}}
  root.querySelector('[data-prev]')?.addEventListener('click',()=>{show(current-1);schedule();});
  root.querySelector('[data-next]')?.addEventListener('click',()=>{show(current+1);schedule();});
  control?.addEventListener('click',()=>{paused=!paused;syncControl();schedule();});
  root.addEventListener('pointerenter',event=>{if(event.pointerType==='mouse'){hovered=true;stop();}});
  root.addEventListener('pointerleave',()=>{hovered=false;schedule();});
  root.addEventListener('focusin',stop);root.addEventListener('focusout',()=>setTimeout(schedule,0));
  document.addEventListener('visibilitychange',schedule);
  root.querySelectorAll('video').forEach(video=>{video.addEventListener('play',stop);video.addEventListener('pause',schedule);video.addEventListener('ended',schedule);});
  motion.addEventListener('change',()=>{paused=motion.matches;syncControl();schedule();});
  syncControl();schedule();
 });
})();
