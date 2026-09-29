@props([
    'label',
    'placeholder' => 'Digite ao menos 2 letras para buscar...',
    'readonly' => false,
    'minChars' => 2,
])

{{--
    Campo de autocomplete: input do Flux + lista própria (no lugar do <datalist> nativo,
    que não aceita estilo). O componente Livewire pai precisa ter:
      - public string $query  (texto exibido no campo)
      - search(string $query): array de ['id', 'label', 'sublabel'?]
      - select(int $id): dispara o evento de seleção
--}}
<div
    x-data="{
        query: @entangle('query'),
        items: [],
        open: false,
        loading: false,
        active: -1,
        searched: false,
        seq: 0,
        minChars: {{ (int) $minChars }},
        onInput() {
            this.searched = false;
            if ((this.query ?? '').trim().length < this.minChars) {
                this.items = [];
                this.open = false;
                return;
            }
            this.loading = true;
            this.open = true;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.fetch(), 250);
        },
        async fetch() {
            const current = ++this.seq;
            const data = await $wire.search(this.query.trim());
            if (current !== this.seq) return;
            this.items = data;
            this.active = data.length ? 0 : -1;
            this.loading = false;
            this.searched = true;
            const exact = data.filter(i => i.label.toLowerCase() === this.query.trim().toLowerCase());
            if (exact.length === 1) this.pick(exact[0]);
        },
        pick(item) {
            this.query = item.label;
            this.open = false;
            this.items = [];
            $wire.select(item.id);
        },
        move(step) {
            if (!this.items.length) return;
            this.open = true;
            this.active = (this.active + step + this.items.length) % this.items.length;
            this.$nextTick(() => this.$refs.list?.children[this.active]?.scrollIntoView({ block: 'nearest' }));
        },
        enter(e) {
            if (this.open && this.items[this.active]) {
                e.preventDefault();
                this.pick(this.items[this.active]);
            }
        },
        highlight(text) {
            const term = (this.query ?? '').trim();
            const escape = s => s.replace(/[&<>]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
            if (!term) return escape(text);
            const i = text.toLowerCase().indexOf(term.toLowerCase());
            if (i < 0) return escape(text);
            return escape(text.slice(0, i)) + '<mark class=&quot;bg-transparent font-semibold text-zinc-900 dark:text-white&quot;>' + escape(text.slice(i, i + term.length)) + '</mark>' + escape(text.slice(i + term.length));
        },
    }"
    x-on:click.outside="open = false"
    x-on:keydown.escape="open = false"
    class="relative w-full"
>
    <flux:field class="w-full">
        <flux:label>{{ $label }}</flux:label>

        <flux:input
            x-model="query"
            x-on:input="onInput()"
            x-on:focus="if (items.length) open = true"
            x-on:keydown.arrow-down.prevent="move(1)"
            x-on:keydown.arrow-up.prevent="move(-1)"
            x-on:keydown.enter="enter($event)"
            x-on:keydown.tab="open = false"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            role="combobox"
            icon="magnifying-glass"
            :readonly="$readonly"
        >
            <x-slot name="iconTrailing">
                <flux:icon.loading x-show="loading" x-cloak class="size-4" />
            </x-slot>
        </flux:input>
    </flux:field>

    <ul
        x-ref="list"
        x-show="open && !loading && items.length"
        x-cloak
        x-transition.opacity.duration.100ms
        role="listbox"
        class="absolute z-50 mt-1 max-h-64 w-full overflow-y-auto rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
    >
        <template x-for="(item, index) in items" :key="item.id">
            <li
                role="option"
                :aria-selected="index === active"
                x-on:mousedown.prevent="pick(item)"
                x-on:mouseenter="active = index"
                :class="index === active ? 'bg-zinc-100 dark:bg-zinc-700' : ''"
                class="cursor-pointer px-3 py-2 text-sm text-zinc-700 dark:text-zinc-200"
            >
                <div x-html="highlight(item.label)"></div>
                <div x-show="item.sublabel" x-text="item.sublabel" class="text-xs text-zinc-500 dark:text-zinc-400"></div>
            </li>
        </template>
    </ul>

    <div
        x-show="open && !loading && searched && !items.length"
        x-cloak
        class="absolute z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-500 shadow-lg dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400"
    >
        Nenhum resultado encontrado
    </div>
</div>
