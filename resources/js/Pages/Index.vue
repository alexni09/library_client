<script setup>
import { onMounted, ref, toRaw, onBeforeUnmount, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import dayjs from 'dayjs'
const clientName = computed(() => usePage().props.clientName)
const styleNumber = computed(() => Number(usePage().props.styleNumber))
const fontNumber = computed(() => Number(usePage().props.fontNumber))
var intervalLines = null
const lines = ref(null)
const fetchLines = () => {
    axios.get('/api/monitor')
        .then((response) => {
            lines.value = toRaw(response.data.data)
        })
        .catch(function (error) {
            console.log(error)
        })
}
onMounted(() => {
    fetchLines()
    intervalLines = setInterval(fetchLines, 2500)
})
onBeforeUnmount(() => {
    clearInterval(intervalLines)
})
</script>
<template>
    <div class="text-lg" :class="{
            'text-zinc-700': styleNumber === 1, 
            'text-cyan-800': styleNumber === 2,
            'font-oswald': fontNumber === 1,
            'font-titilliumweb': fontNumber === 2,
            'font-librebaskerville': fontNumber === 3,
            'font-firasanscondensed': fontNumber === 4,
            'font-yanonekaffeesatz': fontNumber === 5,
            'font-archivonarrow': fontNumber === 6,
            'font-khand': fontNumber === 7,
            'font-voltaire': fontNumber === 8,
            'font-mirza': fontNumber === 9,
            'font-geo text-xl': fontNumber === 10,
            'font-amarante': fontNumber === 11,
            'font-sharetech': fontNumber === 12,
            'font-iceland': fontNumber === 13,
            'font-genos': fontNumber === 14
        }">
        <div class="flex justify-center">
            <h1 class="mt-2 mb-4 font-bold text-4xl">:<span class="ml-1">:</span> {{ clientName }} <span class="mr-1">:</span>:</h1>
        </div>
        <div class="flex justify-center">
            <table :class="{'bg-zinc-50 border border-zinc-500': styleNumber === 1, 'bg-slate-50 border border-slate-500': styleNumber === 2}">
                <tr>
                    <th class="p-1 font-semibold border-b whitespace-nowrap"
                        :class="{'bg-zinc-300 border-zinc-500': styleNumber === 1, 'bg-slate-300 border-slate-500': styleNumber === 2}">When ?</th>
                    <th v-if="styleNumber === 1" class="thStyleOne">Command</th>
                    <th v-if="styleNumber === 2" class="thStyleTwo">Command</th>
                    <th v-if="styleNumber === 1" class="thStyleOne">Message</th>
                    <th v-if="styleNumber === 2" class="thStyleTwo">Message</th>
                    <th v-if="styleNumber === 1" class="thStyleOne">Status</th>
                    <th v-if="styleNumber === 2" class="thStyleTwo">Status</th>
                </tr>
                <tr v-if="lines?.length === 0">
                    <td colspan="4" class="p-1 font-medium italic whitespace-nowrap">No Records Found!</td>
                </tr>
                <tr v-for="line in lines" :key="line.id" 
                    :class="{ 'bg-zinc-200': styleNumber === 1 && line.id % 6 > 2, 'bg-slate-200': styleNumber === 2 && line.id % 6 > 2 }">
                    <td class="px-1 whitespace-nowrap">{{ dayjs(line.datetime + '+00:00').format('YYYY/MM/DD HH:mm:ss') }}</td>
                    <td v-if="styleNumber === 1" class="tdStyleOne">{{ line.command }}</td>
                    <td v-if="styleNumber === 2" class="tdStyleTwo">{{ line.command }}</td>
                    <td v-if="styleNumber === 1" class="tdStyleOne">{{ line.message }}</td>
                    <td v-if="styleNumber === 2" class="tdStyleTwo">{{ line.message }}</td>
                    <td v-if="styleNumber === 1" class="tdStyleOne">{{ line.status }}</td>
                    <td v-if="styleNumber === 2" class="tdStyleTwo">{{ line.status }}</td>
                </tr>
            </table>
        </div>
        <div class="h-24" />
    </div>
</template>
<style lang="postcss" scoped>
.tdStyleOne {
    @apply px-1 border-l border-zinc-500;
}
.tdStyleTwo {
    @apply px-1 border-l border-slate-500;
}
.thStyleOne {
    @apply p-1 bg-zinc-300 font-semibold border-l border-b border-zinc-500;
}
.thStyleTwo {
    @apply p-1 bg-slate-300 font-semibold border-l border-b border-slate-500;
}
</style>