 <video id="v_player" class="video-js vjs-big-play-centered skin-blue vjs-16-9" controls preload="auto" playsinline width="640" height="450" poster="{{URL::to('/'.$movies_info->video_image)}}" data-setup="{}" @if(get_player_cong('autoplay')=="true")autoplay="true"@endif>
					
					
			<!-- video source <source src="https://strimafrica.com/upload/creed.mov"  label='Auto' res='360' /> 	-->			  	   
			<source src="{{URL::to('/'.$movies_info->video_url)}}" type="video/mp4" label='Auto' res='360' />  
			
			@if($movies_info->video_quality)
				@if($movies_info->video_url_480)
				<source src="{{URL::to('/'.$movies_info->video_url_480)}}" type='video/mp4' label='480P' res='480'/>
				@endif	
					
				@if($movies_info->video_url_720)	
			    <source src="{{URL::to('/'.$movies_info->video_url_720)}}" type='video/mp4' label='720P' res='720'/>
			    @endif	
			    	
			    @if($movies_info->video_url_1080)	
			    <source src="{{URL::to('/'.$movies_info->video_url_1080)}}" type='video/mp4' label='1080P' res='1080'/>   
				@endif	  
			@endif	  
			 
			<!-- video subtitle -->
			@if($movies_info->subtitle_on_off)
				@if($movies_info->subtitle_url1)
					<track kind="captions" src="{{$movies_info->subtitle_url1}}"  label="{{$movies_info->subtitle_language1?$movies_info->subtitle_language1:'English'}}" default>		
				@endif
				@if($movies_info->subtitle_url2)
					<track kind="captions" src="{{$movies_info->subtitle_url2}}" label="{{$movies_info->subtitle_language2?$movies_info->subtitle_language2:'English'}}">		
				@endif
				@if($movies_info->subtitle_url3)
					<track kind="captions" src="{{$movies_info->subtitle_url3}}"  label="{{$movies_info->subtitle_language3?$movies_info->subtitle_language3:'English'}}">		
				@endif    
			@endif					 
				<!-- worning text if needed -->
				<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
</video>














{{--

 <link rel="stylesheet"  type="text/css"  href="https://unpkg.com/plyr@3/dist/plyr.css" >
<div class="container">
	<video controls crossorigin playsinline autoplay poster="{{URL::to('/'.$movies_info->video_image)}}">
		 <source src="{{URL::to('/'.$movies_info->video_url)}}" type="video/mp4" />  

			@if($movies_info->subtitle_on_off)
				@if($movies_info->subtitle_url1)
					<track kind="captions" src="{{$movies_info->subtitle_url1}}" srclang="en" label="{{$movies_info->subtitle_language1?$movies_info->subtitle_language1:'English'}}" default>		
				@endif
				@if($movies_info->subtitle_url2)
					<track kind="captions" src="{{$movies_info->subtitle_url2}}" srclang="en" label="{{$movies_info->subtitle_language2?$movies_info->subtitle_language2:'English'}}">		
				@endif
				@if($movies_info->subtitle_url3)
					<track kind="captions" src="{{$movies_info->subtitle_url3}}"  srclang="en" label="{{$movies_info->subtitle_language3?$movies_info->subtitle_language3:'English'}}">		
				@endif    
			@endif 
	</video>
</div>
<script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=es6,Array.prototype.includes,CustomEvent,Object.entries,Object.values,URL"></script>
<script src="https://unpkg.com/plyr@3"></script>
<script>
        const player = new Plyr('video', { captions: { active: true } });

        // Add event listener for the 'waiting' event to handle buffering
        player.on('waiting', () => {
            // Show the buffering overlay
            document.getElementById('buffering-overlay').style.display = 'block';
        });

        // Add event listener for the 'playing' event to hide the buffering overlay
        player.on('playing', () => {
            // Hide the buffering overlay
            document.getElementById('buffering-overlay').style.display = 'none';
        });

        // Expose player so it can be used from the console
        window.player = player;
        //player.fullscreen.enter();
    </script> --}}