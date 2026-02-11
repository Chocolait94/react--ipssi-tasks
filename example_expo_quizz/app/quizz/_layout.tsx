import styles from "@/assets/styles/main";
import { Stack } from "expo-router";

export default function QuizzLayout() {
  return (
    <Stack>
      <Stack.Screen name="index" options={{ title: "Bienvenue !" }} />
      <Stack.Screen name="quizz" options={{ title: "Faire un quizz ?" }} />{" "}
      <Stack.Screen
        name="easy"
        options={{
          title: "Petit joueur",
          headerStyle: { backgroundColor: styles.light.easy.backgroundColor },
        }}
      />
      <Stack.Screen
        name="hardcore"
        options={{
          title: "Pour les vrais",
          headerStyle: {
            backgroundColor: styles.light.hardcore.backgroundColor,
          },
        }}
      />
    </Stack>
  );
}
