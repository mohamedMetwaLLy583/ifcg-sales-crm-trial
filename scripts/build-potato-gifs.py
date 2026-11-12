"""Build short, optimized GIFs from the existing official IFCG video."""
from pathlib import Path
import sys, subprocess, json
root=Path(__file__).resolve().parents[1]
sys.path.insert(0,str(root/'.tools/video'))
import imageio_ffmpeg
ffmpeg=imageio_ffmpeg.get_ffmpeg_exe()
out=root/'public/ifcg/images/motion'
out.mkdir(parents=True,exist_ok=True)
source=root/'public/ifcg/images/official-production-4k.webm'
for n,start in enumerate([0.4,3.2,6.2,9.2,12.2,16.2],1):
 base=out/f'potato-{n}'
 common=[ffmpeg,'-hide_banner','-loglevel','error','-y','-ss',str(start),'-i',str(source)]
 subprocess.run(common+['-frames:v','1','-vf','scale=480:-2',str(base)+'.jpg'],check=True)
 filters='fps=8,scale=480:-2:flags=lanczos,split[a][b];[a]palettegen=max_colors=96:stats_mode=diff[p];[b][p]paletteuse=dither=bayer:bayer_scale=3'
 subprocess.run(common+['-t','2','-filter_complex',filters,'-loop','0',str(base)+'.gif'],check=True)
 print(json.dumps({'file':base.name,'bytes':Path(str(base)+'.gif').stat().st_size}),flush=True)
