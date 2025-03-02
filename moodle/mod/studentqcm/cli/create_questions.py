import csv
import random

# Définition des utilisateurs
user_ids = range(3, 103)  # De l'ID 3 à 102

# Définition des types de questions
question_types = ["QCM", "QCM", "QCU", "TCS"]
pop_definitions = {
    1: [("QCM", 1), ("QCU", 1)],
    2: [("QCM", 2), ("QCU", 1)],
    3: [("QCM", 3)],
    4: [("QCU", 3)]
}

# Génération des pop_questions CSV
pop_questions = []
pop_id_counter = 1  # ID pour le CSV des popTypes
pop_id_mapping = {}

for user_id in user_ids:
    for pop_type_id in pop_definitions:
      for questions in pop_definitions[pop_type_id]:
        nb = questions[1]
        for i in range(nb):
          pop_questions.append([pop_id_counter, user_id, pop_type_id])
          pop_id_mapping[(user_id, pop_type_id)] = pop_id_counter
          pop_id_counter += 1

pop_csv_filename = "mdl_studentqcm_pop.csv"
with open(pop_csv_filename, mode="w", newline="", encoding="utf-8") as file:
    writer = csv.writer(file)
    writer.writerow(["id", "userId", "popTypeId"])
    writer.writerows(pop_questions)

print(f"CSV généré : {pop_csv_filename}")

# Génération des questions
questions = []
question_id = 1  # Commence à l'ID 2

for user_id in user_ids:
    # Génération des questions classiques
    for q_type in question_types:
        questions.append([
            question_id, user_id, f"q{question_id%16}", f"comment{question_id%16}", "santé", 1, 1, 1, 0, q_type, 0, "NULL", "NULL"
        ])
        question_id += 1

    nb_pop = 1

    # Génération des questions POP
    for pop_id, pop_content in pop_definitions.items():
        for q_type, count in pop_content:
            for _ in range(count):
                questions.append([
                    question_id, user_id, f"q{question_id}", f"comment{question_id}", "santé", 1, 1, 1, 0, q_type, 1, nb_pop, pop_id
                ])
                question_id += 1
                nb_pop+=1

# Génération des réponses CSV
answers = []
answer_id_counter = 1

for question in questions:
    question_id = question[0]
    is_qcm = question[9] == "QCM"
    correct_answers = 2 if is_qcm else 1
    correct_indices = random.sample(range(5), correct_answers)
    
    for i in range(5):
        is_true = 1 if i in correct_indices else 0
        answers.append([answer_id_counter, question_id, is_true, f"Réponse {i+1}", "Explication", i+1])
        answer_id_counter += 1


# Écriture dans un fichier CSV des questions
csv_filename = "mdl_studentqcm_question.csv"
with open(csv_filename, mode="w", newline="", encoding="utf-8") as file:
    writer = csv.writer(file)
    writer.writerow(["id", "userid", "question", "global_comment", "context", "competency", "subcompetency", "referentiel", "status", "type", "isPop", "popId", "popTypeId"])
    writer.writerows(questions)

answers_csv_filename = "mdl_studentqcm_answer.csv"
with open(answers_csv_filename, mode="w", newline="", encoding="utf-8") as file:
    writer = csv.writer(file)
    writer.writerow(["id", "question_id", "isTrue", "answer", "explanation", "indexation"])
    writer.writerows(answers)

