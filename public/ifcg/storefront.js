(() => {
 const menus=[...document.querySelectorAll('.store-dropdown')];
 const fine=matchMedia('(hover:hover) and (pointer:fine)');
 function close(except) { menus.forEach(menu=>{if(menu!==except)menu.open=false;}); }
 menus.forEach(menu=>{
  let timer;
  const summary=menu.querySelector('summary');
  menu.addEventListener('toggle',()=>{summary.setAttribute('aria-expanded',String(menu.open));if(menu.open)close(menu);});
  summary.setAttribute('aria-expanded',String(menu.open));
  summary.addEventListener('click',()=>clearTimeout(timer));
  menu.addEventListener('pointerenter',()=>{clearTimeout(timer);if(fine.matches)timer=setTimeout(()=>{close(menu);menu.open=true;},140);});
  menu.addEventListener('pointerleave',()=>{if(fine.matches)timer=setTimeout(()=>{if(!menu.contains(document.activeElement))menu.open=false;},160);});
  menu.addEventListener('focusout',()=>{setTimeout(()=>{if(!menu.contains(document.activeElement))menu.open=false;},0);});
 });
 document.addEventListener('click',event=>{if(!event.target.closest('.store-dropdown'))close();});
 document.addEventListener('keydown',event=>{if(event.key==='Escape'){const open=menus.find(menu=>menu.open);close();open?.querySelector('summary').focus();}});
})();
