// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.psi;

import java.util.List;
import org.jetbrains.annotations.*;
import com.intellij.psi.PsiElement;

public interface LgcMathRuleBlock extends PsiElement {

  @NotNull
  List<LgcArithmeticPredicate> getArithmeticPredicateList();

  @NotNull
  LgcEnd getEnd();

  @Nullable
  PsiElement getRuleName();

}
