<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD con Vue.js y Vuetify</title>
    <link href="https://cdn.jsdelivr.net/npm/vuetify@3.7.3/dist/vuetify.min.css" rel="stylesheet">
    <style>
        .container {
            display: flex;
            justify-content: space-between;
            padding: 20px;
        }
        .form-container {
            flex: 1;
            margin-right: 20px;
        }
        .table-container {
            flex: 1;
        }
    </style>
</head>

<body>
    <div id="app">
        <v-app>
            <div class="container">
                <div class="form-container">
                    <v-card>
                        <v-card-title>
                            <h2>@{{ editing ? 'Editar candidato' : 'Agregar candidato' }}</h2>
                        </v-card-title>
                        <v-card-text>
                            <v-form ref="form" v-model="valid">
                                <v-text-field v-model="candidate.name" label="Nombre" required variant="outlined"></v-text-field>
                                <v-text-field v-model="candidate.phone" label="Teléfono" required variant="outlined"></v-text-field>
                                <v-text-field v-model="candidate.profession" label="Ocupación" required variant="outlined"></v-text-field>
                                <v-file-input v-model="candidate.cv" label="Subir CV (PDF)" accept=".pdf"
                                              @change="handleFileUpload" variant="outlined" prepend-icon=""></v-file-input>
                                <v-btn @click="editing ? updateCandidate() : saveCandidate()" color="primary">
                                    @{{ editing ? 'Actualizar' : 'Guardar' }}
                                </v-btn>
                                <v-alert class="mt-2" v-if="message" :type="isSuccess ? 'success' : 'error'" dismissible>
                                    @{{ message }}
                                </v-alert>
                            </v-form>
                        </v-card-text>
                    </v-card>
                </div>

                <div class="table-container">
                    <v-card>
                        <v-card-title>
                            <h2>Lista de candidatos</h2>
                        </v-card-title>
                        <v-card-text>
                            <v-data-table :headers="headers" :items="candidates">
                                <template v-slot:item.actions="{ item }">
                                    <v-btn @click="editCandidate(item)" color="yellow" class="my-1 mx-1">Editar</v-btn>
                                    <v-btn @click="deletePerson(item.id)" color="red" class="my-1 mx-1">Eliminar</v-btn>
                                    <v-btn v-if="item.cv_path" @click="downloadCV(item.id)" color="green" class="my-1 mx-1">Descargar CV</v-btn>
                                </template>
                            </v-data-table>
                        </v-card-text>
                    </v-card>
                </div>
            </div>
        </v-app>
    </div>

    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@3.7.3/dist/vuetify.min.js"></script>
    <script>
        const { createApp, ref } = Vue;
        const { createVuetify } = Vuetify;

        const vuetify = createVuetify();

        createApp({
            setup() {
                const candidate = ref({
                    name: '',
                    phone: '',
                    profession: '',
                    cv: null
                });
                const candidates = ref([]);
                const message = ref('');
                const isSuccess = ref(true);
                const valid = ref(false);
                const headers = [
                    { text: 'Nombre', value: 'name' },
                    { text: 'Teléfono', value: 'phone' },
                    { text: 'Ocupación', value: 'profession' },
                    { text: 'Acciones', value: 'actions', sortable: false },
                ];
                const editing = ref(false);
                const currentCandidateId = ref(null);

                const handleFileUpload = (event) => {
                    //candidate.value.cv = event;
                    candidate.value.cv = event.target.files[0]; // Accede al archivo subido
                };

                const fetchCandidates = async () => {
                    const response = await fetch('/api/candidates');
                    candidates.value = await response.json();
                };

                const saveCandidate = async () => {
                    const formData = new FormData();
                    formData.append('name', candidate.value.name);
                    formData.append('phone', candidate.value.phone);
                    formData.append('profession', candidate.value.profession);
                    if (candidate.value.cv) formData.append('cv', candidate.value.cv);

                    const response = await fetch('/api/candidates', {
                        method: 'POST',
                        body: formData
                    });

                    if (response.ok) {
                        message.value = 'Datos guardados exitosamente!';
                        isSuccess.value = true;
                        resetForm();
                    } else {
                        const errors = await response.json();
                        message.value = 'Error al guardar: ' + Object.values(errors.errors).map(e => e[0]).join(', ');
                        isSuccess.value = false;
                    }
                    fetchCandidates();
                };

                const deletePerson = async (id) => {
                    if (confirm('¿Estás seguro de que deseas eliminar esta candidata?')) {
                        await fetch(`/api/candidates/${id}`, { method: 'DELETE' });
                        fetchCandidates();
                    }
                };

                const editCandidate = (item) => {
                    candidate.value = { ...item };
                    editing.value = true;
                    currentCandidateId.value = item.id;
                };

                const updateCandidate = async () => {
                    const formData = new FormData();
                    formData.append('name', candidate.value.name);
                    formData.append('phone', candidate.value.phone);
                    formData.append('profession', candidate.value.profession);
                    if (candidate.value.cv) formData.append('cv', candidate.value.cv);

                    const response = await fetch(`/api/candidates/${currentCandidateId.value}`, {
                        method: 'POST',
                        body: formData,
                    });

                    if (response.ok) {
                        message.value = 'Candidata actualizada exitosamente!';
                        isSuccess.value = true;
                        resetForm();
                    } else {
                        const errors = await response.json();
                        message.value = 'Error al actualizar: ' + Object.values(errors.errors).map(e => e[0]).join(', ');
                        isSuccess.value = false;
                    }
                    fetchCandidates();
                };

                const resetForm = () => {
                    candidate.value = { name: '', phone: '', profession: '', cv: null };
                    editing.value = false;
                };

                const downloadCV = async (id) => {
                    const response = await fetch(`/api/candidates/${id}/download-cv`);
                    if (response.ok) {
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = 'cv.pdf';
                        link.click();
                        window.URL.revokeObjectURL(url);
                    }
                };

                fetchCandidates();

                return {
                    candidate,
                    candidates,
                    message,
                    isSuccess,
                    valid,
                    headers,
                    editing,
                    saveCandidate,
                    deletePerson,
                    editCandidate,
                    updateCandidate,
                    handleFileUpload,
                    downloadCV
                };
            }
        }).use(vuetify).mount('#app');
    </script>
</body>
</html>
