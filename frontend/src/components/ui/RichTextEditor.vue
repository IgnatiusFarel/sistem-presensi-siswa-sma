<template>
  <div class="rich-text-editor">
    <QuillEditor
      v-model:content="content"
      :options="editorOptions"
      content-type="html"
      theme="snow"
      style="height: 400px"
      placeholder="Tuliskan isi konten berita di sini..."
      @ready="onEditorReady"
    />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";
import Api from "@/services/Api";
import { useMessage } from "naive-ui";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue"]);

const content = ref(props.modelValue);
const editorReady = ref(false);
const quillRef = ref(null);

const message = useMessage();
let promptTriggered = false;

const editorOptions = ref({
  modules: {
    toolbar: [
      [{ font: [] }, { size: [] }],
      ["bold", "italic", "underline", "strike"],
      [{ color: [] }, { background: [] }],
      [{ script: "sub" }, { script: "super" }],
      [{ header: 1 }, { header: 2 }, "blockquote", "code-block"],
      [
        { list: "ordered" },
        { list: "bullet" },
        { indent: "-1" },
        { indent: "+1" },
      ],
      [{ direction: "rtl" }, { align: [] }],
      ["link", "image", "video"],
      ["clean"],
    ],
    history: {
      delay: 1000,
      maxStack: 50,
      userOnly: true,
    },
  },
  placeholder: "Tulis sesuatu di sini...",
  readOnly: false,
  theme: "snow",
});

watch(
  content,
  (newValue) => {
    emit("update:modelValue", newValue);
  },
  { deep: true }
);

watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue !== content.value) {
      content.value = newValue;
    }
  }
);

onMounted(() => {
  content.value = props.modelValue;
});

const handlePromptIfAny = async () => {
  if (!quillRef.value) return;

  const quill = quillRef.value; 
  const text = quill.getText().trimEnd();
  const match = text.match(/\/\/(.{3,})$/);

  if (match && !promptTriggered) {
    promptTriggered = true;

    const prompt = match[1].trim();
    message.info("[AI] Menghasilkan teks...");

    try {
      const res = await Api.post("/ai/generate", { prompt });

      if (res?.data?.output) {
        const aiText = res.data.output.trim();
        const newText = text.replace(/\/\/(.{3,})$/, aiText);
        quill.clipboard.dangerouslyPasteHTML(0, newText);
        emit("update:modelValue", quill.root.innerHTML);
        message.success("[AI] Teks berhasil digenerate!");
      } else {
        message.error("[AI] Respon kosong.");
        console.warn("Respon:", res);
      }
    } catch (err) {
      message.error("[AI] Gagal request ke API");
      console.error("Error:", err);
    }

    setTimeout(() => {
      promptTriggered = false;
    }, 1000);
  } else {
    console.log("[AI] Tidak ditemukan prompt atau sedang menunggu...");
  }
};

const onEditorReady = (quill) => {
  editorReady.value = true;
  quillRef.value = quill;
  console.log("[AI] Editor ready!");

  quill.root.setAttribute("tabindex", "0");
  quill.root.focus();

  quill.root.addEventListener("keydown", async (e) => {
    console.log("[AI] Key pressed:", e.key);

    if (e.key === "Tab") {
      e.preventDefault();
      console.log("[AI] Tab pressed, running handlePromptIfAny...");
      await handlePromptIfAny();
    }
  });
};
</script>

<style scoped>
.rich-text-editor {
  border: 1px solid #d9d9d9;
  border-radius: 6px;
  overflow: hidden;
}

.rich-text-editor :deep(.ql-editor) {
  min-height: 200px;
  font-size: 14px;
  line-height: 1.5;
}

.rich-text-editor :deep(.ql-toolbar) {
  border-bottom: 1px solid #d9d9d9;
  background-color: #fafafa;
}

.rich-text-editor :deep(.ql-container) {
  border: none;
  font-size: 14px;
}

.dark .rich-text-editor {
  border: 1px solid #444;
}

.dark .rich-text-editor :deep(.ql-toolbar) {
  background-color: #2a2a2a;
  border-bottom: 1px solid #444;
}

.dark .rich-text-editor :deep(.ql-container) {
  background-color: #1e1e1e;
  color: #f1f1f1;
}

.dark .rich-text-editor :deep(.ql-editor) {
  background-color: #1e1e1e;
  color: #f1f1f1;
}

.dark .rich-text-editor :deep(.ql-editor.ql-blank::before) {
  color: #aaa; /* placeholder */
}

.dark .rich-text-editor :deep(.ql-snow .ql-stroke) {
  stroke: #fff !important;
}
.dark .rich-text-editor :deep(.ql-snow .ql-fill) {
  fill: #fff !important;
}
.dark .rich-text-editor :deep(.ql-snow .ql-picker-label) {
  color: #fff !important;
}
</style>
