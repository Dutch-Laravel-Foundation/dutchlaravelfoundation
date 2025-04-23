<?php
  use Illuminate\Support\Facades\Http;

  $response = Http::get('https://dlf.vragen.ai/display/standaard/oembed');

  $html = json_decode($response->body())->html;

  echo '<div x-data="{ showVragenAi: false }" @open-vragen-ai.window="showVragenAi = true"
    @close-vragen-ai.window="showVragenAi = false" x-cloak x-show="showVragenAi" role="dialog" aria-modal="true"
    x-on:keydown.escape.prevent.stop="showVragenAi = false" x-transition.opacity id="vragenai-app-container"
    class="px-2 flex fixed top-0 left-0 w-screen justify-center items-center h-screen bg-black/20 z-1000"
    @click.self="$dispatch(\'close-vragen-ai\')">
    <div class="w-full max-w-screen-md bg-white/50 rounded-2xl p-1 shadow-xl">
      <div
        class="w-full h-[calc(100vh-1.5rem)] sm:max-h-[800px] overflow-y-auto bg-white rounded-xl p-2 border border-gray-100">
        <div class="flex flex-row justify-between items-center mb-2 px-4 py-2">
          <h3 class="my-0 font-bold">Zoeken</h3>
          <button class="text-black/50 cursor-pointer text-sm font-medium hover:text-black"
            @click="$dispatch(\'close-vragen-ai\')">
            Sluiten
          </button>
        </div>
        <div>
          <style>
            .vragenai-question-form,
            .vragenai-question-form:focus {
              border-radius: 6px !important;
              outline-color: #FF2D20 !important;
            }
            .vragenai-questions__item-list {
              padding: 0 !important;
            }
          </style>
          <div class="px-4">
             '. $html .'
          </div>
        </div>
      </div>
    </div>
</div>';
?>