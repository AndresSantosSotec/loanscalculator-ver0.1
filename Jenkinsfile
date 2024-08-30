//Clonar el repositorio
stage('Cloning Repository') {
    steps {
        git 'https://github.com/AndresSantosSotec/loanscalculator-ver0.1.git'
    }
}

//Copilacion del codigo
stage('Build') {
    steps {
        sh 'php -l src/*.php'
    }
}

//Analisis de vulnerabilidades con dependecy track
stage('Dependency Track Analysis') {
    steps {
        sh """
        curl -X POST 'http://192.168.1.65:8081/api/v1/scan' \ 
        -H 'X-API-Key: your-dependency-track-api-key' \
        -F 'project=project-uuid' \
        -F 'file=@path/to/your/project.zip'
        """
    }

    //ahi donde dice localhost debe ir la ip de la maquina virtual, es decir la direccion en la que podemos entrar a dependecy track.
}

//Informe de la tarea en pandoc
stage('Generate Report') {
    steps {
        sh 'pandoc results.json -o report.pdf'
    }
}

//almacenamiento del informe
stage('Archive Report') {
    steps {
        archiveArtifacts artifacts: 'report.pdf', allowEmptyArchive: true
    }
}