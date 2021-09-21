pipeline {
    agent {
        label 'master'
    }

    stages {
        stage('Test') {
            steps {
                sh 'composer install'
                sh './vendor/bin/phpcs -d date.timezone=UTC'
            }
        }

        stage('Build') {
            steps {
                sh 'yarn'
                sh 'gulp build'
            }
        }
    }
}