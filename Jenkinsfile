pipeline {
    agent any

    stages {

        stage('Clone Repository') {
            steps {
                git branch: 'main', url: 'https://github.com/UdayShankarPandey/rescue_coordination.git'
            }
        }

        stage('Build Docker Image') {
            steps {
                sh 'docker build -t rescue-app .'
            }
        }

        stage('Stop Old Container') {
            steps {
                sh 'docker stop rescue-container || true'
                sh 'docker rm rescue-container || true'
            }
        }

        stage('Run New Container') {
            steps {
                sh 'docker run -d -p 8000:80 --name rescue-container rescue-app'
            }
        }

    }
}
