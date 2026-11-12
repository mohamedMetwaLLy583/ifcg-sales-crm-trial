const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const events=()=>({handlers:{},addEventListener(k,v){this.handlers[k]=v;}});
let jobs=new Map(),sequence=0;
const controls=Object.fromEntries(['data-prev','data-next','data-counter','data-autoplay'].map(k=>[k,{...events(),dataset:{playLabel:'Play',pauseLabel:'Pause'},setAttribute(k,v){this[k]=v;}}]));
const slides=Array.from({length:3},(_,i)=>({hidden:i>0,querySelectorAll(){return [];}}));
const root={...events(),contains(){return false;},querySelectorAll(s){return s==='[data-slide]'?slides:[];},querySelector(s){return controls[s.slice(1,-1)];}};
const document={...events(),hidden:false,activeElement:null,querySelectorAll(){return [root];}};
const motion={...events(),matches:false};
vm.runInNewContext(fs.readFileSync('public/ifcg/slider.js','utf8'),{document,matchMedia:()=>motion,setTimeout(fn,ms){jobs.set(++sequence,{fn,ms});return sequence;},clearTimeout(id){jobs.delete(id);}});
function tick(){const [id,job]=[...jobs][0];jobs.delete(id);job.fn();}
assert.equal([...jobs.values()][0].ms,1500);tick();assert.equal(slides[1].hidden,false);tick();assert.equal(slides[2].hidden,false);tick();assert.equal(slides[0].hidden,false);
root.handlers.pointerenter({pointerType:'mouse'});assert.equal(jobs.size,0);root.handlers.pointerleave();assert.equal(jobs.size,1);
controls['data-autoplay'].handlers.click();assert.equal(jobs.size,0);assert.equal(controls['data-autoplay']['aria-pressed'],'true');controls['data-autoplay'].handlers.click();assert.equal(jobs.size,1);
document.hidden=true;document.handlers.visibilitychange();assert.equal(jobs.size,0);document.hidden=false;document.handlers.visibilitychange();assert.equal(jobs.size,1);
motion.matches=true;motion.handlers.change();assert.equal(jobs.size,0);
console.log('PASS: 1500ms interval, 3-slide wraparound, hover pause/resume, toggle, hidden tab and reduced motion.');
