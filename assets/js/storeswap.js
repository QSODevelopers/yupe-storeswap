

$(document).ready(function(){
	
	var sseConsole = Object.create(SSEConsole)
		.construct('.console',{
			status:'#consoleStatus',
			bundle:'#consoleBundle',
			body:'#consoleBody',
			progress:'#consoleProgress',
		});

	$('#clearConsole').on('click',function(){sseConsole.clearBody()});


	$('.update-base').on('click',function(){
		sseConsole.startBenchmark();

		var o = $(this);
		$('.update-base, .flushAction').attr("disabled", true);

		var totalCount = 0,
			totalDeleteCount = 0,
			totalCreateCount = 0,
			totalUpdateCount = 0;

		evtSource = new EventSource(o.attr('data-url'));

		evtSource.addEventListener("message", function(e) {
		    data = JSON.parse(e.data);
			sseConsole.addMessage(data['title'], data['message'], data['context']);
		}, false);

		evtSource.addEventListener("error", function(e) {
			if (this.readyState == EventSource.CONNECTING) {
				sseConsole.addMessage(
						"Связь потеряна.", 
						"Оставайтесь на связи, надеемся она вскоре востановится.", 
						'danger')
						.setStatus('Связь с сервером потеряна');
			 } else {
			    evtSource.close();
				benchmark = sseConsole.stopBenchmark();
				sseConsole.addMessage(
							"Ошибка на сервере.", 
							this, 
							'danger')
							.setStatus('Ошибка')
					   		.deleteProgressBar('parsingBar')
					   		.deleteProgressBar('deleteBar')
					   		.deleteProgressBar('createrBar')
					   		.deleteProgressBar('updaterBar')
					   		.deleteProgressBar('nonChangeBar');
				$('.update-base, .flushAction').attr("disabled", false);
			 }
		}, false);

		evtSource.addEventListener("PHP_ERROR", function(e) {
			data = JSON.parse(e.data);
			sseConsole.addMessage(
				data['title'], 
				data['message'], 
				data['context'])
				.setStatus('На сервере произошла ошибка');
			evtSource.close();
			benchmark = sseConsole.stopBenchmark();
			sseConsole.addMessage(
		    	"Процесс завершен с ошибкой.", 
		    	"Время выполнения: "+benchmark.getHoursTwoDigits()+':'+benchmark.getMinutesTwoDigits()+':'+benchmark.getSecondsTwoDigits(), 
		    	'info')
		   		.clearStatus()
		   		.deleteProgressBar('parsingBar')
		   		.deleteProgressBar('deleteBar')
		   		.deleteProgressBar('createrBar')
		   		.deleteProgressBar('updaterBar')
		   		.deleteProgressBar('nonChangeBar');
			$('.update-base, .flushAction').attr("disabled", false);
		}, false);

		evtSource.addEventListener("CLOSE", function(e) {
			evtSource.close();
			benchmark = sseConsole.stopBenchmark();
		    sseConsole.addMessage(
		    	"Процесс завершен.", 
		    	"Время выполнения: "+benchmark.getHoursTwoDigits()+':'+benchmark.getMinutesTwoDigits()+':'+benchmark.getSecondsTwoDigits(), 
		    	'info')
		   		.clearStatus()
		   		.deleteProgressBar('parsingBar')
		   		.deleteProgressBar('deleteBar')
		   		.deleteProgressBar('createrBar')
		   		.deleteProgressBar('updaterBar')
		   		.deleteProgressBar('nonChangeBar');
			$('.update-base, .flushAction').attr("disabled", false);
		}, false);

		evtSource.addEventListener("CHANGE_STATUS", function(e) {
			data = JSON.parse(e.data);
			sseConsole.setStatus(data['title']);
		}, false);

		evtSource.addEventListener("INIT_PARSER", function(e) {
			data = JSON.parse(e.data);
			totalCount = parseInt(data['message']);
			sseConsole.addProgressBar('parsingBar','info', true)
						.addMessage(
							data['title'], 
							data['message'] +' записей', 
							data['context'])
						.setStatus('Парсинг записей с Simaland');
		}, false);

		evtSource.addEventListener("PARSING_PROCESS", function(e) {
			data = JSON.parse(e.data);
			sseConsole.setProgress(Math.persent(parseInt(data['message']), totalCount))
						.addMessage(
							data['title'],
							'Полученно записей '+data['message']+' из '+totalCount,
							data['context']);							
		}, false);

		evtSource.addEventListener("PARSING_END", function(e) {
			data = JSON.parse(e.data);
			title = data['title'];
			message = data['message'];
			context = data['context'];
			sseConsole.addMessage(
						data['title'],
						'',
						data['context']);
		}, false);

		evtSource.addEventListener("START_DELIMIT", function(e) {
			data = JSON.parse(e.data);
			sseConsole.addMessage(data['title'], data['message'], data['context'])
					.setStatus('Сравнение данных');
		}, false);

		evtSource.addEventListener("INIT_DELETER", function(e) {
			data = JSON.parse(e.data);
			totalDeleteCount = parseInt(data['message']);
			sseConsole.addProgressBar('deleteBar','danger')
						.addMessage(
							data['title'],
							'Найдено записей: '+ data['message'],
							data['context']);
		}, false);

		evtSource.addEventListener("INIT_CREATER", function(e) {
			data = JSON.parse(e.data);
			totalCreateCount = parseInt(data['message']);
			sseConsole.addProgressBar('createrBar','success')
						.addMessage(
							data['title'],
							'Найдено записей: '+ data['message'], 
							data['context']);
		}, false);

		evtSource.addEventListener("INIT_UPDATER", function(e) {
			data = JSON.parse(e.data);
			totalUpdateCount = parseInt(data['message']);
			sseConsole.addProgressBar('updaterBar','warning')
						.addMessage(
							data['title'],
							'Найдено записей: '+ data['message'], 
							data['context']);
		}, false);

		evtSource.addEventListener("INIT_UNCHANGER", function(e) {
			data = JSON.parse(e.data);
			unchnage = totalUpdateCount + totalCreateCount + totalDeleteCount;
			sseConsole.addProgressBar('nonChangeBar','info', false, 'deleteBar')
						.setProgress(Math.persent(totalCount - unchnage, totalCount))
						.addMessage(
							data['title'], 
							totalCount - unchnage, 
							data['context']);
			totalUpdateCount = totalCreateCount  = totalDeleteCount = 0;
		}, false);

		evtSource.addEventListener("DELETER_IN_PROGRESS", function(e) {
			data = JSON.parse(e.data);
			totalDeleteCount += parseInt(data['title']);
			sseConsole.toggleBar('deleteBar')
						.setProgress(Math.persent(totalDeleteCount, totalCount))
			data['message'].forEach(function(item){
				sseConsole.addMessage(
							item.title,
							item.message,
							item.context
						);
			})								
		}, false);

		evtSource.addEventListener("CREATER_IN_PROGRESS", function(e) {
			data = JSON.parse(e.data);
			totalCreateCount += parseInt(data['title']);
			sseConsole.toggleBar('createrBar')
						.setProgress(Math.persent(totalCreateCount, totalCount));
			data['message'].forEach(function(item){
				sseConsole.addMessage(
							item.title,
							item.message,
							item.context
						);
			})						
		}, false);

		evtSource.addEventListener("UPDATER_IN_PROGRESS", function(e) {
			data = JSON.parse(e.data);
			totalUpdateCount += parseInt(data['title']);
			sseConsole.toggleBar('updaterBar')
						.setProgress(Math.persent(totalUpdateCount, totalCount));
			data['message'].forEach(function(item){
				sseConsole.addMessage(
							item.title,
							item.message,
							item.context
						);
			})						
		}, false);

	});

	
})