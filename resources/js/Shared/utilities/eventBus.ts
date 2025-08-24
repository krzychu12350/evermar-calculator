import { ref, watch } from 'vue';

const bus = ref(new Map());

// Create and export the useEventable function for components to use
export function useEventable() {
  // Emit event and data
   function emit(event: string, ...args: any[]) {
    bus.value.set(event, args);
  }

  // Watch for an event and run a callback when it's triggered
  function watchEvent(event: string, callback: Function) {
    watch(
      () => bus.value.get(event),
      (newData) => {
        if (newData) {
          callback(newData[0]);
        }
      }
    );
  }

  return {
    emit,
    watchEvent,
    bus,
  };
}
