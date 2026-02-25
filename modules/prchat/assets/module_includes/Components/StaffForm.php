<?php $instance = &get_instance(); ?>
<form hidden enctype="multipart/form-data" name="fileForm" method="post" onsubmit="uploadFileForm(this);return false;">
     <input type="file" class="file" name="userfile" required />
     <input type="submit" name="submit" class="save" value="save" />
     <input type="hidden" name="<?php echo $instance->security->get_csrf_token_name(); ?>" value="<?php echo $instance->security->get_csrf_hash(); ?>">
</form>

<form method="post" enctype="multipart/form-data" name="pusherMessagesForm" id="pusherMessagesForm" onsubmit="return false;">
     <div class="message-input">
			
				<button class="icon-btn" title="Upload Image">
				<i class="fa-regular fa-file-image attachment fileUpload" data-container="body" data-toggle="tooltip" title="<?php echo _l('chat_file_upload'); ?>" aria-hidden="true"></i>
				</button>
               <textarea type="text" disabled name="msg" id="chatInput" rows="1" class="group_chatbox chatbox ays-ignore" placeholder="<?= _l('chat_type_a_message'); ?>"></textarea>

               <input type="hidden" class="ays-ignore from" name="from" value="" />
               <input type="hidden" class="ays-ignore to" name="to" value="" />
               <input type="hidden" class="ays-ignore typing" name="typing" value="false" />
               <input type="hidden" class="ays-ignore" name="<?php echo $instance->security->get_csrf_token_name(); ?>" value="<?php echo $instance->security->get_csrf_hash(); ?>">
               

               <?php loadChatComponent('MicrophoneIcon'); ?>

              <span class="wrap"> <?php loadChatComponent('SearchMessages', ['props' => 'search_messages']);  ?></span>

               <input type="hidden" class="ays-ignore has_newmessages" id="" value="false" />
               <button class="submit enterBtn icon-btn" name="enterBtn"><i class="fa-solid fa-paper-plane"></i></button>
			   
     </div>
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
	#frame .content .message-input .wrap .search_client_messages, #frame .content .message-input .wrap .search_messages {
     position: absolute; */
     width: unset !important; 
    height: unset !important; 
     right: unset !important; 
     top: unset !important; 
    fill: #6986e8 !important;   
}
#frame .content .message-input .wrap .search_client_messages, #frame .content .message-input .wrap .search_messages{
	    width: 25px !important;
		position: static !important;
}
</style>

<script>
    const group_chatbox = document.querySelector('.group_chatbox');
    group_chatbox.addEventListener('input', () => {
      group_chatbox.style.height = 'auto';
      group_chatbox.style.height = Math.min(group_chatbox.scrollHeight, 120) + 'px';
    });
  </script>