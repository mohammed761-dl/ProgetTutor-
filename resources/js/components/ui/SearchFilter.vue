<script setup>
import { Search, X } from "lucide-vue-next";
import Input from "@/components/ui/input/Input.vue";
import Button from "@/components/ui/button/Button.vue";

const props = defineProps({
    searchQuery: {
        type: String,
        default: ""
    },
    searchPlaceholder: {
        type: String,
        default: "Search..."
    },
    filters: {
        type: Array,
        default: () => []
    },
    showClearButton: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:searchQuery', 'update:filter', 'clear']);

const updateSearch = (value) => {
    emit('update:searchQuery', value);
};

const updateFilter = (filterKey, value) => {
    emit('update:filter', { key: filterKey, value });
};

const clearAll = () => {
    emit('clear');
};
</script>

<template>
    <div class="flex flex-col sm:flex-row gap-4 p-4 bg-gray-50 rounded-lg">
        <!-- Search Input -->
        <div class="flex-1">
            <div class="relative">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                <Input
                    :model-value="searchQuery"
                    @update:model-value="updateSearch"
                    :placeholder="searchPlaceholder"
                    class="pl-10 pr-10"
                />
                <button
                    v-if="searchQuery"
                    @click="updateSearch('')"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>
        </div>
        
        <!-- Dynamic Filters -->
        <div 
            v-for="filter in filters" 
            :key="filter.key"
            :class="filter.width || 'min-w-[160px]'"
        >
            <select
                :value="filter.value"
                @change="updateFilter(filter.key, $event.target.value)"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">{{ filter.placeholder || 'All' }}</option>
                <option 
                    v-for="option in filter.options" 
                    :key="option.value" 
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>
        
        <!-- Clear Filters Button -->
        <Button
            v-if="showClearButton"
            @click="clearAll"
            variant="outline"
            class="px-4"
        >
            <X class="w-4 h-4 mr-2" />
            Clear
        </Button>
    </div>
</template>
