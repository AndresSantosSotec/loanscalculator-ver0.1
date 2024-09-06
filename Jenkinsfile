pipeline {
    agent any

    environment {
        DEP_TRACK_URL = 'http://192.168.1.65:8082/api/v1'  // Cambia a la URL de tu API Dependency Track.
        PROJECT_ID = 'febdf5a7-a141-486f-ad3c-30ae459c0b81'  // El ID del proyecto en Dependency Track.
    }

    stages {
        stage('Clonar repositorio') {
            steps {
                git branch: 'main', url: 'https://github.com/AndresSantosSotec/loanscalculator-ver0.1.git'
            }
        }

        stage('Instalar dependencias de Composer') {
            steps {
                sh 'composer install'
            }
        }

        stage('Generar archivo BOM') {
            steps {
                sh 'composer require --dev cyclonedx/cyclonedx-php-composer'
                sh 'composer make-bom'
            }
        }

        stage('Subir BOM a Dependency Track') {
            steps {
                script {
                    def bomFile = 'bom.xml'
                    sh """
                    curl -X PUT "${DEP_TRACK_URL}/bom" \
                        -F "project=${PROJECT_ID}" \
                        -F "bom=@${bomFile}"
                    """
                }
            }
        }
    }

    post {
        success {
            echo 'Análisis de vulnerabilidades completado con éxito.'
        }
        failure {
            echo 'Falló el análisis de vulnerabilidades.'
        }
    }
}