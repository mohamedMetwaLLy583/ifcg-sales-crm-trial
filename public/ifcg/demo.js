(() => {
 const key='ifcg-basket';
 const read=()=>{try{const b=JSON.parse(localStorage.getItem(key)||'[]');return Array.isArray(b)?b.filter(x=>Number.isInteger(x.id)&&Number.isFinite(Number(x.quantity))&&Number(x.quantity)>0):[];}catch{return [];}};
 let basket=read();
 const save=()=>{localStorage.setItem(key,JSON.stringify(basket));document.querySelectorAll('.basket-count').forEach(e=>e.textContent=basket.length);};
 save();
 document.querySelectorAll('[data-add]').forEach(button=>button.addEventListener('click',()=>{
  const id=Number(button.dataset.add);if(!basket.some(x=>x.id===id))basket.push({id,quantity:100});save();
  button.textContent=button.dataset.added+' ✓';button.classList.add('is-added');
  if(document.getElementById('rfq-items'))render();
 }));
 const picker=document.getElementById('request-product');
 if(picker)picker.addEventListener('change',()=>{const button=document.getElementById('request-add');button.dataset.add=picker.value;button.textContent=button.dataset.original;button.classList.remove('is-added');});
 const pickerButton=document.getElementById('request-add');if(pickerButton)pickerButton.dataset.original=pickerButton.textContent;
 const root=document.getElementById('rfq-items');
 if(!root)return;
 const products=JSON.parse(document.getElementById('rfq-data').textContent);
 const labels=JSON.parse(document.getElementById('rfq-labels').textContent);
 const old=JSON.parse(document.getElementById('rfq-old').textContent);
 if(Array.isArray(old))basket=old.map(x=>({id:Number(x.product_id),quantity:Number(x.quantity)}));
 basket=basket.filter(x=>products.some(p=>p.id===x.id));save();
 function render(){
  root.replaceChildren();
  basket.forEach((item,index)=>{
   const product=products.find(p=>p.id===item.id);
   const row=document.createElement('div');row.className='rfq-item';
   const img=document.createElement('img');img.src=product.image;img.alt=product.name;
   const content=document.createElement('div');const title=document.createElement('strong');title.textContent=product.name;
   const hidden=document.createElement('input');hidden.type='hidden';hidden.name='items['+index+'][product_id]';hidden.value=item.id;
   const label=document.createElement('label');label.textContent=labels.quantity;
   const quantity=document.createElement('input');quantity.type='number';quantity.step='0.001';quantity.min='0.001';quantity.max='1000000';quantity.required=true;quantity.name='items['+index+'][quantity]';quantity.value=item.quantity;
   quantity.addEventListener('input',()=>{item.quantity=quantity.value;save();});
   label.append(quantity);content.append(title,hidden,label);
   const remove=document.createElement('button');remove.type='button';remove.className='remove';remove.textContent='×';remove.setAttribute('aria-label',labels.remove+' '+product.name);
   remove.addEventListener('click',()=>{basket=basket.filter(x=>x.id!==item.id);save();render();});
   row.append(img,content,remove);root.append(row);
  });
  document.getElementById('rfq-empty').hidden=basket.length>0;
  document.getElementById('rfq-submit').disabled=basket.length===0;
 }
 render();
})();

