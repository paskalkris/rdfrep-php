document.onkeydown = function checkKeycode(event){
    test();
    /*var keycode, keyChar;
    if (!event) var event = window.event;
    if (event.keyCode) keycode = event.keyCode;
    else if (event.which) keycode = event.which;
    
    keyChar = String.fromCharCode(keycode);
    alert("Нажат символ: " + keycode);*/
}
// Инициализация редактора
onload = function(){
   wysiwyg_init('wysiwyg_textarea', 'wysiwyg_iframe')
}

// Функции инициализации на вход мы даем id составляющих редактор textarea и iframe
function wysiwyg_init(textarea_id, iframe_id){
   var textarea = document.getElementById(textarea_id)
   var iframe = document.getElementById(iframe_id)
   // Проверим на существование iframe и textarea
   // Через offsetWidth проверим видимость iframe – то есть редактор находится в визуальном режиме
   if(iframe && textarea && iframe.offsetWidth){
      iframe.contentWindow.document.designMode = 'On'
      // Для Gecko устанавливаем такой режим, чтобы форматирование ставилось тегами, а не стилями
      // Чтобы MSIE не выдавал ошибку, прячем это в конструкцию try-catch
      try{
         //iframe.contentWindow.document.execCommand("useCSS", false, true)
      }catch(e){}

      // Копируем текст из textarea в iframe
      wysiwyg_textarea2iframe(textarea_id, iframe_id)
      set_cursor_to_first_child(iframe.contentWindow)
      /*iframe_id.selectionStart = 100;
      iframe_id.selectionEnd = 100;
      iframe_id.focus()*/
   }
}

// Копирование текста из textarea в iframe
function wysiwyg_textarea2iframe(textarea_id, iframe_id){
   try{
      document.getElementById(iframe_id).contentWindow.document.body.innerHTML = document.getElementById(textarea_id).value
   }catch(e){
      setTimeout("wysiwyg_textarea2iframe('" + textarea_id + "', '" + iframe_id + "')", 0)
   }
}

// Переключение редактора из визуального режима в HTML-режим и обратно
function wysiwyg_switch_mode(textarea_id, iframe_id){
   var textarea = document.getElementById(textarea_id)
   var iframe = document.getElementById(iframe_id)
   if(iframe && textarea){
      // редактор в режиме редактирования HTML-source
      if(textarea.offsetWidth){
         // Сначала показываем iframe, потом прячем textarea.
         // Такой порядок для того, чтобы прокрутка не перескакивала
         // из-за укоротившейся на миг страницы.
         iframe.style.display = ''
         textarea.style.display = 'none'
         wysiwyg_init(textarea_id, iframe_id)
         iframe.focus()
      }else{ // Редактор в визуальном режиме
         textarea.style.display = ''         
         iframe.style.display = 'none'
         textarea.value = iframe.contentWindow.document.body.innerHTML
         textarea.focus()
      }
   }
}

function test(){
   var iframe = document.getElementById('wysiwyg_iframe')

   var bounds = get_selection_bounds(iframe.contentWindow)
   if(!bounds) return null
   var property = bounds['start'].getAttribute('property')
   //if (!property) return null
   
   //+Сделать поиск ближайшего тега с аттрибутом about
//   var subject = closest_parent_by_tag_name(bounds['start'], 'div').getAttribute('about')
   var subject = closest_parent_by_attr_name(bounds['start'], 'about').getAttribute('about')
   var predicate = bounds['start'].getAttribute('property')
   var object = bounds['start'].text.replace(/(^\s+|\s+$)/g, '')
   if (confirm(subject + "\n" + predicate + "\n" + object + "\nПродолжить?")) {
       sendData(subject, predicate, object)
   }
   //alert('root = '+bounds['root'].getAttribute('property')+'\nstart = '+bounds['start']+'\nend='+bounds['end'])
}

//Установить курсор на первый тег
function set_cursor_to_first_child(editor_window){
   var range, start, child

   if(editor_window.getSelection){ // Gecko, Opera
      var selection = editor_window.getSelection()
      // Выделение, вообще говоря, может состоять из нескольких областей.
      // Но при написании редактора нас это не должно заботить, берем 0-ую:
      range = selection.getRangeAt(0)

      start = range.startContainer
      child = closest_child_by_tag_name(start, "#text");
      range.selectNode(child);
      selection.removeAllRanges()
      selection.addRange(range)
      selection.collapseToStart()
      
   }
}

// Взятие крайних узлов выделения (корня — root и самых крайних "слева" и "справа" — start и end)
// на вход даем окно (т.е. iframe.contentWindow)
function get_selection_bounds(editor_window){
   var range, root, start, end

   if(editor_window.getSelection){ // Gecko, Opera
      var selection = editor_window.getSelection()
      // Выделение, вообще говоря, может состоять из нескольких областей.
      // Но при написании редактора нас это не должно заботить, берем 0-ую:
      range = selection.getRangeAt(0)

      start = range.startContainer
      end = range.endContainer
      root = range.commonAncestorContainer

      if(start.nodeName.toLowerCase() == "body") return null
      // если узлы текстовые, берем их родителей
      if(start.nodeName == "#text") start = start.parentNode
      if(end.nodeName == "#text") end = end.parentNode

      if(start == end) root = start

      return {
         root: root,
         start: start,
         end: end
      }

   }else if(editor_window.document.selection){ // MSIE
      range = editor_window.document.selection.createRange()
      if(!range.duplicate) return null
      
      var r1 = range.duplicate()
      var r2 = range.duplicate()
      r1.collapse(true)
      r2.moveToElementText(r1.parentElement())
      r2.setEndPoint("EndToStart", r1)
      start = r1.parentElement()
      
      r1 = range.duplicate()
      r2 = range.duplicate()
      r2.collapse(false)
      r1.moveToElementText(r2.parentElement())
      r1.setEndPoint("StartToEnd", r2)
      end = r2.parentElement()
      
      root = range.parentElement()
      if(start == end) root = start
      
      return {
         root: root,
         start: start,
         end: end
      }
   }
   return null // браузер, не поддерживающий работу с выделением
}

var global_stage // некрасивая глобальная переменная
// bounds — массив [root, start, end]
// tag_name — имя тега
// остальные аргументы не указываем, используются для рекурсии
function find_tags_in_subtree(bounds, tag_name, stage, second){
   var root = bounds['root']
   var start = bounds['start']
   var end = bounds['end']

   if(start == end) return [start]

   if(!second) global_stage=stage

   if(global_stage == 2) return []
   if(!global_stage) global_stage = 0

   tag_name = tag_name.toLowerCase()

   var nodes=[]
   for(var node = root.firstChild; node; node = node.nextSibling){
      if(node==start && global_stage==0){
         global_stage = 1
      }
      if(node.nodeName.toLowerCase() == tag_name && node.nodeName != '#text' || tag_name == ''){
         if(global_stage == 1){
            nodes.push(node)
         }
      }
      if(node==end && global_stage==1){
         global_stage = 2
      }
      nodes=nodes.concat(find_tags_in_subtree({root:node, start:start, end:end}, tag_name, global_stage, true))
   }
   return nodes
}

// Ближайший родитель с нужным тегом
function closest_parent_by_tag_name(node, tag_name){
   tag_name = tag_name.toLowerCase()
   var p = node
   do{
      if(tag_name == '' || p.nodeName.toLowerCase() == tag_name) return p
   }while(p = p.parentNode)

   return node
}

// Ближайший потомок с нужным тегом (может не сработать, т.к. просматривается дерево из первых потомков)
function closest_child_by_tag_name(node, tag_name){
   tag_name = tag_name.toLowerCase()
   var p = node
   do{
      if(tag_name == '' || p.nodeName.toLowerCase() == tag_name) return p
   }while(p = p.firstChild)

   return node
}

// Ближайший родитель с нужным атрибутом
function closest_parent_by_attr_name(node, attr_name){
   attr_name = attr_name.toLowerCase()
   var p = node
   do{
      if(attr_name == '' || p.getAttribute(attr_name)) return p
   }while(p = p.parentNode)

   return node
}

function get_selected_tags(editor_window, tag_name){
   if(tag_name){
      tag_name = tag_name.toLowerCase()
   }else{
      tag_name = ''
   }
   var bounds = get_selection_bounds(editor_window)
   if(!bounds) return null

   bounds['start'] = closest_parent_by_tag_name(bounds['start'], tag_name)
   bounds['end'] = closest_parent_by_tag_name(bounds['end'], tag_name)
   return find_tags_in_subtree(bounds, tag_name)
}

// Оформляем выделение нужным блочным тегом с нужным классом
function wysiwyg_format_block(iframe_id, tag_name, class_name){
   var iframe = document.getElementById(iframe_id)
   var wysiwyg = iframe.contentWindow.document
   // Оформляем нужным блочным тегом
   wysiwyg.execCommand('formatblock', false, '<' + tag_name + '>')
   // Выбираем из выделения все теги нужного имени и ставим им класс
   var nodes = get_selected_tags(iframe.contentWindow, tag_name)
   for(var i = 0; i < nodes.length; i++){
      if(class_name){
         // Устанавливаем класс
         nodes[i].className = class_name
      }else{
         // Убираем класс, если он нам не нужен
         nodes[i].removeAttribute('class')
         nodes[i].removeAttribute('className')
      }
   }
   iframe.focus()
}

// "Магический" неиспользуемый цвет
var magic_unusual_color='#00f001'
// Оформляем выделение нужным строковым (инлайновым) тегом с нужным классом
function format_inline(iframe_id, tag_name, class_name){
   var iframe = document.getElementById(iframe_id)
   var wysiwyg = iframe.contentWindow.document
   // Убираем все существующее форматирование
   wysiwyg.execCommand('RemoveFormat', false, true)
   // В MSIE после RemoveFormat остаются span-ы, удалим их тоже
   clean_nodes(get_selected_tags(iframe.contentWindow, 'span'))

   // Если имя тега не указано (применяется, когда мы хотим просто убрать форматирование)
   if(tag_name!=''){
      // Вставляем наш <font color>
      wysiwyg.execCommand('ForeColor', false, magic_unusual_color)

      // Заменяем узлы, образованные font'ами, на новые с нужным именем и классом
      var nodes=get_selected_tags(iframe.contentWindow, 'font')
      var new_node
      for(var i=0;i<nodes.length;i++){
         if(nodes[i].getAttribute('color') != magic_unusual_color) continue
         new_node = wysiwyg.createElement(tag_name)
         if(class_name) new_node.className = class_name
         new_node.innerHTML = nodes[i].innerHTML
         nodes[i].parentNode.replaceChild(new_node, nodes[i])
      }
   }
   iframe.focus()
}

// Чистка узлов (удаляем тег, оставляем содержимое)
// (Только для MSIE)
function clean_nodes(nodes, class_name){
   if(!nodes) return
   var l = nodes.length - 1
   for(var i = l ; i >= 0 ; i--){
      if(!classname || nodes[i].className == class_name){
         nodes[i].removeNode(false)
      }
   }
}

// Работа со списками. Передаем на вход одну из команд:
// ul, ol, indent, outdent
function list(iframe_id, command){
   var iframe = document.getElementById(iframe_id)
   var wysiwyg = iframe.contentWindow.document
   switch(command){
      case 'ol':
         wysiwyg.execCommand('InsertOrderedList')
         break
      case 'ul':
         wysiwyg.execCommand('InsertUnorderedList')
         break
      case 'indent':
         wysiwyg.execCommand('Indent')
         // удаляем <BLOCKQUOTE>
         clean_nodes(get_selected_tags(iframe.contentWindow, 'blockquote'))
         break
      case 'outdent':
         wysiwyg.execCommand('Outdent')
         break
      
   }
   iframe.focus()
}

/* Данная функция создаёт кроссбраузерный объект XMLHTTP */
function getXmlHttp() {
   var xmlhttp;
   try {
      xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
   } catch (e) {
   try {
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
   } catch (E) {
      xmlhttp = false;
   }
   }
   if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
      xmlhttp = new XMLHttpRequest();
   }
   return xmlhttp;
}

function sendData(subject, predicate, object) {
   var xmlhttp = getXmlHttp(); // Создаём объект XMLHTTP
   xmlhttp.open('POST', 'test.php', true); // Открываем асинхронное соединение
   xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); // Отправляем кодировку
   xmlhttp.send("subject=" + encodeURIComponent(subject) + 
                "&predicate=" + encodeURIComponent(predicate) + 
                "&object=" + encodeURIComponent(object)); // Отправляем POST-запрос
   xmlhttp.onreadystatechange = function() { // Ждём ответа от сервера
      if (xmlhttp.readyState == 4) { // Ответ пришёл
         if(xmlhttp.status == 200) { // Сервер вернул код 200 (что хорошо)
            alert("Операция выполнена успешно!");
            //alert(xmlhttp.responseText);
            //document.getElementById("wysiwyg_iframe").innerHTML = xmlhttp.responseText; // Выводим ответ сервера
         }
      }
   };
}
