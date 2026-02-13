<?php

Route::get('chatbot', 'ChatbotController@inicio');
Route::post('chatbot/send_chat', 'ChatbotController@send_chat');
Route::get('chatbot/cargar_chatbot', 'ChatbotController@cargar_chatbot');
Route::get('chatbot/export_excel', 'ChatbotController@exportExcel');
Route::post('chatbot/cambiar_reporte', 'ChatbotController@cambiar_reporte');
