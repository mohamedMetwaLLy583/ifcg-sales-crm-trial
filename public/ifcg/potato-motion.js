(() => {
 const preference=matchMedia('(prefers-reduced-motion: reduce)');
 document.querySelectorAll('[data-motion-section]').forEach(section=>{
  const button=section.querySelector('[data-motion-toggle]');
  const images=[...section.querySelectorAll('[data-gif]')];
  let playing=!preference.matches;
  const visible=new Set();
  function paint(){
   button.textContent=playing?button.dataset.pause:button.dataset.play;
   button.setAttribute('aria-pressed',String(playing));
   images.forEach(img=>{
    const src=playing&&visible.has(img)&&!document.hidden?img.dataset.gif:img.dataset.still;
    if(img.getAttribute('src')!==src)img.src=src;
   });
  }
  if('IntersectionObserver' in window){
   const observer=new IntersectionObserver(entries=>{entries.forEach(entry=>entry.isIntersecting?visible.add(entry.target):visible.delete(entry.target));paint();},{threshold:.1});
   images.forEach(img=>observer.observe(img));
  }else images.forEach(img=>visible.add(img));
  button.addEventListener('click',()=>{playing=!playing;paint();});
  preference.addEventListener('change',()=>{playing=!preference.matches;paint();});
  document.addEventListener('visibilitychange',paint);
  paint();
 });
})();