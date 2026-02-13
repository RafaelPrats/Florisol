<div class="col-md-12" id="div_foto">
    <input type="hidden" id="face_mode" value="user">
    <div style="width:{{isPC() ? '80%' : '100%'}};margin:0 auto">
        <video autoplay id="video-image" class="hide my-3" style="margin: 15px 0px;display: none"></video>
        <canvas id="picture-img" class="hide" style="width: 100%; height:400px"></canvas>
        <img id="screenshot-image hide" class="screenshot-image hide" alt="">
        <button class="btn-camera hide" id="div_tomar_foto" onclick="tomar_foto()">
            <i class="fa fa-camera"></i>
        </button>
        <button class="btn-camera-flip" id="div_tomar_foto" onclick="cambiar_camara()">
            <i class="fa fa-refresh"></i>
        </button>
    </div>
</div>

<script>

    shouldFaceUser = false;

    constarints ={
        audio: false,
        video: {
            width: 600,
            height: 400,
            //facingMode: 'user'
        }
    }

    abrir_camara()

    function abrir_camara(){
            
        if ('mediaDevices' in navigator && 'getUserMedia' in navigator.mediaDevices) {
            constarints.video.facingMode = $("#face_mode").val()
            navigator.mediaDevices.getUserMedia(constarints)
            star_stream()

        }

    }

    async function star_stream(){

        constarints.video.facingMode = $("#face_mode").val()
        stream = await navigator.mediaDevices.getUserMedia(constarints)
        video = document.querySelector('video')
        video.setAttribute('autoplay', '');
        video.setAttribute('muted', '');
        video.setAttribute('playsinline', '')
       
        $("#div_fin_captura, #screenshot-image, #div_tomar_foto, #video-image, #picture-img").removeClass('hide')
        video.play()

        if ('srcObject' in video) {
            video.srcObject = stream;
        } else {
            video.src = URL.createObjectURL(stream);
        }

        //model = blazeface.load();

        video.addEventListener("loadeddata", async () => {
            // wait for blazeface model to load
            //model = await blazeface.load();
            // call the function
            idIterval = setInterval(()=>{detect_faces()}, 100)
        })
        
    }

    
    async function detect_faces (val1=120,val2=40) {
        console.log(val1,val2)
        //const prediction = await model.estimateFaces(video, false);   
    
        let canvas = document.getElementById('picture-img')
        let ctx = canvas.getContext("2d")

        // draw the video first
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height)

        /*prediction.forEach((pred) => {

            // draw the rectangle enclosing the face
            ctx.beginPath();
            ctx.lineWidth = "4"
            ctx.strokeStyle = "blue"
            // the last two arguments are width and height
            // since blazeface returned only the coordinates, 
            // we can find the width and height by subtracting them.
            ctx.rect(
                pred.topLeft[0]-val1,
                pred.topLeft[1]-val1,
                pred.bottomRight[0] - pred.topLeft[0]-val2,
                pred.bottomRight[1] - pred.topLeft[1]-val2
            )
            
            ctx.stroke()
            
     
        })*/

    }

    function tomar_foto() {

        let canvas = document.getElementById('picture-img')
        let screenshotImage = $("#screenshot-image")
        canvas.width = video.videoWidth
        canvas.height = video.videoHeight
        canvas.getContext('2d').drawImage(video, 0, 0)
        screenshotImage.src = canvas.toDataURL('image/png')
        screenshotImage.removeClass('hide')

        let data= {
            _token: '{{csrf_token()}}',
            photo : canvas.toDataURL('image/png'), 
        }
        $('#div_foto').LoadingOverlay('show');

        $.post('{{url('control_diario/compare_photo')}}', data, function (retorno) {
            
            if (retorno.success) {

                mini_alerta('success', retorno.mensaje, 5000)
                
                if($(".id_personal_detalle").length){

                    $.each($(".id_personal_detalle"),(i,j)=>{
                        
                        if($(j).val() == retorno.personal.id_personal_detalle){

                            $(j).parent().find('input.input-date-cd').val($("#desde_masivo").val())
                            $(j).parent().find('input.input-date-ch').val($("#hasta_masivo").val())
                            $(j).parent().find('select.id_mano_obra option[value='+retorno.personal.id_mano_obra+']').attr('selected',true)
                            
                            return false

                        }else{

                            add_control_personal(retorno.personal.identificacion)
                            return false
                        }

                    })

                }else{
                
                    add_control_personal(retorno.identificacion)

                }

                clearInterval(idIterval)
                setTimeout(() => { 
                    idIterval = setInterval(function(){ detect_faces(0,0) }, 100) 
                }, 1000)
                
            } else {

                alerta(retorno.mensaje)

            }
            $('#div_foto').LoadingOverlay('hide');

        }, 'json').fail(function (retorno) {
            
            $('#div_foto').LoadingOverlay('hide');
            alerta_errores(retorno.responseText);
            alerta('Ha ocurrido un problema al enviar la información');
        })


    }

    function cerrar_camara() {

        if(idIterval!=null)  clearInterval(idIterval)
        video.srcObject.getTracks()[0].stop()

    }

    function cambiar_camara(){
        cerrar_camara()
        
        $("#face_mode").val( $("#face_mode").val() == 'user' ? 'environment' : 'user' )
        
        abrir_camara()
    }

    $("#btn_cerrar_modal_view_foto_asistencia").on('click', function (e) {
        cerrar_camara()
    });
</script>