(() => {
 const reduced=window.matchMedia('(prefers-reduced-motion: reduce)');
 if('IntersectionObserver' in window && !reduced.matches){
  const observer=new IntersectionObserver(entries=>entries.forEach(entry=>{
   if(entry.isIntersecting){entry.target.classList.remove('reveal-pending');entry.target.classList.add('reveal-ready');observer.unobserve(entry.target);}
  }),{threshold:0.05,rootMargin:'0px 0px 30px 0px'});
  document.querySelectorAll('[data-reveal],.corporate-gallery>a,.resource-card,.supply-steps>article').forEach(el=>{
   if(el.getBoundingClientRect().top>window.innerHeight){el.classList.add('reveal-pending');observer.observe(el);}
  });
  reduced.addEventListener('change',event=>{if(event.matches){document.querySelectorAll('.reveal-pending').forEach(el=>el.classList.remove('reveal-pending'));observer.disconnect();}});
 }
 document.querySelectorAll('[data-gallery]').forEach(gallery=>{
  const thumbs=[...gallery.querySelectorAll('[data-gallery-thumb]')];
  const main=gallery.querySelector('[data-gallery-main]');
  const modal=gallery.querySelector('[data-lightbox]');
  const large=gallery.querySelector('[data-lightbox-image]');
  const opener=gallery.querySelector('[data-gallery-open]');
  let active=0;
  function show(index){
   active=(index+thumbs.length)%thumbs.length;
   main.src=thumbs[active].dataset.galleryThumb;large.src=main.src;
   thumbs.forEach((button,i)=>button.setAttribute('aria-pressed',String(i===active)));
   gallery.querySelector('[data-gallery-count]').textContent=String(active+1).padStart(2,'0')+' / '+String(thumbs.length).padStart(2,'0');
   gallery.querySelector('[data-lightbox-count]').textContent=(active+1)+' / '+thumbs.length;
  }
  thumbs.forEach((button,index)=>button.addEventListener('click',()=>show(index)));
  opener.addEventListener('click',()=>{if(typeof modal.showModal==='function')modal.showModal();else window.open(main.src,'_blank','noopener');});
  gallery.querySelector('[data-lightbox-close]').addEventListener('click',()=>modal.close());
  modal.addEventListener('close',()=>opener.focus());
  modal.addEventListener('click',event=>{if(event.target===modal){const r=modal.getBoundingClientRect();if(event.clientX<r.left||event.clientX>r.right||event.clientY<r.top||event.clientY>r.bottom)modal.close();}});
  gallery.querySelector('[data-gallery-prev]').addEventListener('click',()=>show(active-1));
  gallery.querySelector('[data-gallery-next]').addEventListener('click',()=>show(active+1));
  modal.addEventListener('keydown',event=>{if(event.key==='ArrowRight'){event.preventDefault();show(active+1);}if(event.key==='ArrowLeft'){event.preventDefault();show(active-1);}});
 });
})();