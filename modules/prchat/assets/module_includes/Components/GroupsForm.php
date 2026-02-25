<?php $instance = &get_instance(); ?>
<form hidden enctype="multipart/form-data" name="groupFileForm" id="groupFileForm" method="post" onsubmit="uploadGroupFileForm(this);return false;">
     <input type="file" class="file" name="userfile" required />
     <input type="submit" name="submit" class="save" value="save" />
     <input type="hidden" name="<?php echo $instance->security->get_csrf_token_name(); ?>" value="<?php echo $instance->security->get_csrf_hash(); ?>">
</form>
<form hidden method="post" enctype="multipart/form-data" name="groupMessagesForm" id="groupMessagesForm" onsubmit="return false;">
     <div class="message-input group_msg_input">
   
				<button class="icon-btn" title="Upload Image">
				<i class="fa-regular fa-file-image attachment groupFileUpload" data-container="body" data-toggle="tooltip" title="<?php echo _l('chat_file_upload'); ?>" aria-hidden="true"></i></button>
               <textarea type="text" name="g_message" rows="1" class="group_chatbox ays-ignore mention" placeholder="<?= _l('chat_type_a_message_mention'); ?>"></textarea>
               <input type="hidden" class="ays-ignore from" name="from" value="" />
               <input type="hidden" class="ays-ignore typing" name="typing" value="false" />
               <input type="hidden" class="ays-ignore" name="<?php echo $instance->security->get_csrf_token_name(); ?>" value="<?php echo $instance->security->get_csrf_hash(); ?>">
               
               <?php loadChatComponent('MicrophoneIcon'); ?>
               <button class="submit enterGroupBtn icon-btn" name="enterGroupBtn"> <i class="fa-solid fa-paper-plane"></i></button>
      
     </div>
	 
	 
	 <!--<div class="message-input">
		<button class="icon-btn" title="Upload Image">
		  <i class="fa-regular fa-image"></i>
		</button>
	 
		<textarea class="chatbox" placeholder="Type a message..." rows="1"></textarea>
	 
		<button class="icon-btn" title="Record Audio">
		  <i class="fa-solid fa-microphone"></i>
		</button>
	 
		<button class="icon-btn submit" title="Send Message">
		  <i class="fa-solid fa-paper-plane"></i>
		</button>
  </div>-->
	 
</form>
<style>.message-input {
      background-color: #fff;
      border: 1px solid #ddd;
      border-radius: 30px;
      display: flex;
      align-items: center;
      padding: 10px 15px;
      margin: 0 auto;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }
 
    .message-input textarea {
      flex: 1;
      resize: none;
      border: none;
      padding: 10px 12px;
      font-size: 15px;
      border-radius: 20px;
      max-height: 120px;
      min-height: 36px;
      overflow-y: auto;
      background-color: #f0f0f0;
      margin: 0 10px;
      transition: all 0.2s;
    }
 
    .message-input textarea:focus {
      outline: none;
      background-color: #fff;
    }
 
    .icon-btn {
      background: none;
      border: none;
      cursor: pointer;
      padding: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      color: #555;
      transition: color 0.2s;
    }
 
    .icon-btn:hover {
      color: #007bff;
    }
 
    .submit {
      background-color: #007bff;
      color: #fff;
      border-radius: 50%;
      padding: 10px;
    }
 
    .submit:hover {
      background-color: #0056b3;
    }
	.group_chatbox {
		margin-inline:0!important;
	}
	.mentions-input-box{
		width:100%!important;
	}
	#frame .content .startMic {
    position: static !important;
    width: 22px !important;
   
    right: unset !important;
    top: unset !important;
	fill: #6986e8 !important;
}
</style>

<script>
    const textarea = document.querySelector('.group_chatbox');
    textarea.addEventListener('input', () => {
      textarea.style.height = 'auto';
      textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    });
  </script>